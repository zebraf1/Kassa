<?php
namespace Rotalia\InventoryBundle\Controller;

use Exception;
use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Form\ProductFilterType;
use Rotalia\InventoryBundle\Model\ProductQuery;
use Rotalia\InventoryBundle\Model\SettingQuery;
use Rotalia\InventoryBundle\Model\Transaction;
use Rotalia\InventoryBundle\Model\TransactionPeer;
use Rotalia\UserBundle\Model\MemberQuery;
use Rotalia\UserBundle\Model\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class PurchaseController
 * @package Rotalia\InventoryBundle\Controller
 */
class PurchaseController extends DefaultController
{
    public function homeAction(Request $request)
    {
        if (!$this->isGranted(User::ROLE_USER) && $this->getPos($request) === null) {
            throw new AccessDeniedException();
        }
        return $this->render('RotaliaInventoryBundle:Purchase:home.html.twig', [
            'pos' => $this->getPos($request),
            'form' => $this->createForm(new ProductFilterType(true))->createView(),
            'member' => $this->getMember()
        ]);
    }
    /**
     * Purchase products with credit or cash, add credit by paying cash to point of sale
     *
     * @ApiDoc (
     *   resource = false,
     *   section="Purchase",
     *   description = "Creates transactions, reduces member credit and product storage count",
     *   requirements={
     *     {"name"="payment","requirement"="cash|credit|refund","description"="Payment type"}
     *   },
     *   parameters={
     *      {"name"="memberId","dataType"="integer","required"=false,"description"="Member ID of the buyer"},
     *      {"name"="conventId","dataType"="integer","required"=false,"description"="Convent ID where to reduce inventory. PointOfSale convent is always used if available. Default member convent ID"},
     *      {"name"="basket","dataType"="Object","required"=false,"description"="Not required for refund payment"},
     *      {"name"="basket[0][id]","dataType"="int","required"=true,"description"="Product ID"},
     *      {"name"="basket[0][count]","dataType"="float","required"=true,"description"="Count purchased"},
     *      {"name"="basket[0][price]","dataType"="float","required"=true,"description"="Price of the product when added to cart"},
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when input data has errors. If basket item price changes, basket must be synced",
     *     403 = "Returned when user is not logged in and browser is not point of sale",
     *     500 = "Returned when the transaction fails, all actions are reverted. Try again or report a problem",
     *   }
     * )
     * @param Request $request
     * @param $payment
     * @return JSendResponse
     */
    public function paymentAction(Request $request, $payment)
    {
        switch (strtolower($payment)) {
            case 'cash':
                // Put cash into register to pay for products
                $transactionType = Transaction::TYPE_CASH_PURCHASE;
                $paymentType = 'Sularahamakse';
                break;
            case 'credit':
                // Use credit balance to pay for products
                $transactionType = Transaction::TYPE_CREDIT_PURCHASE;
                $paymentType = 'Krediidimakse';
                break;
            case 'refund':
                // Put money into cash register to receive credit
                $transactionType = Transaction::TYPE_CASH_PAYMENT;
                $paymentType = 'Krediidi lisamine';
                break;
            default:
                return JSendResponse::createError('Vigane makseviis: '.$payment, 400);
                break;
        }

        $currentMember = $this->getMember();
        $member = null;

        $memberId = $request->get('memberId');
        $basket = $request->get('basket');

        if ($payment !== 'refund') {
            if ($basket === null) {
                return JSendResponse::createFail('Ostukorv puudub', 400);
            }
            if (!is_array($basket)) {
                return JSendResponse::createFail('Vigased ostukorvi andmed', 400);
            }
        }

        // Get Point of Sale
        $pos = $this->getPos($request);

        // User must be logged in or using a point of sale to proceed
        if ($currentMember) {
            $member = $currentMember;
        } elseif ($pos === null) {
            return JSendResponse::createFail('Ostmiseks peab olema sisse logitud', 403);
        }

        // Temporarily authenticated user via PoS
        if ($memberId) {
            $member = MemberQuery::create()->findPk($memberId);
            if ($member === null) {
                return JSendResponse::createFail('Kasutajat ei leitud', 400);
            }
        }

        if ($member === null && $pos === null) {
            return JSendResponse::createError('Tehing ei ole lubatud, logi sisse', 403);
        }

        if ($member === null && $payment !== 'cash') {
            return JSendResponse::createError($paymentType.' nõuab sisse logimist', 403);
        }

        if ($pos === null && $payment !== 'credit') {
            return JSendResponse::createError($paymentType.' nõuab kassat (näiteks konvendi arvuti)', 403);
        }

        // Use integer for summarizing double values (see: programming floating point issue)
        $totalSumCents = 0;
        $connection = \Propel::getConnection(TransactionPeer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
        $connection->beginTransaction();

        if ($pos !== null) {
            $conventId = $pos->getConventId();
        } else {
            $conventId = $request->get('conventId', $member->getKoondisedId());
        }

        try {
            if ($payment === 'refund') {
                // If sum is positive then cash was put in, otherwise cash was taken out
                $sum = doubleval($request->get('sum'));
                if ($sum == 0) {
                    return JSendResponse::createError('Sisesta summa', 400);
                }
                $transaction = new Transaction();
                $transaction->setSum($sum);
                $transaction->setMemberRelatedByCreatedBy($currentMember);
                $transaction->setMemberRelatedByMemberId($member);
                $transaction->setConventId($conventId);
                $transaction->setType($transactionType);
                $transaction->save($connection);
                // credit balance changes the same way (positive cash = positive credit). This sum is deducted from balance
                $totalSumCents = (int)(100 * -$sum);
            } else {
                // Create transactions for all purchases
                foreach ($basket as $item) {
                    try {
                        $this->validateBasketItem($item);
                    } catch (Exception $e) {
                        return JSendResponse::createError('Ostukorvis on vigane toode: '.$e->getMessage(), 400);
                    }
                    $transaction = new Transaction();
                    $product = ProductQuery::create()->findPk($item['id']);
                    $product->setConventId($conventId);
                    $transaction->setCount($item['count']);
                    $transaction->setProduct($product);
                    $transaction->setCurrentPrice($product->getPrice());
                    $transaction->setMemberRelatedByCreatedBy($currentMember);
                    $transaction->setMemberRelatedByMemberId($member);
                    $transaction->setConventId($conventId);
                    $totalSumCents += (int)(100 * $transaction->calculateSum());
                    $transaction->setType($transactionType);
                    $transaction->save($connection);
                    $product->getProductInfo()->reduceStorageCount($item['count']);
                    $product->save();
                }
            }

            // Reduce member credit
            if ($payment !== 'cash') {
                $memberCredit = $member->getCredit($conventId);
                $memberCredit->adjustCredit(-$totalSumCents / 100);
                $memberCredit->save($connection);
            }

            // Add convent cash
            if ($payment !== 'credit') {
                $setting = SettingQuery::getCurrentCashSetting($conventId);
                $currentCash = doubleval($setting->getValue()) * 100;
                $currentCash += $totalSumCents;
                $setting->setValue($currentCash / 100);
                $setting->save();
            }

            $connection->commit();

            return JSendResponse::createSuccess([
                'totalSumCents' => $totalSumCents,
                'newCredit' => $member->getTotalCredit()
            ]);
        } catch (Exception $e) {
            $connection->rollBack();
            return JSendResponse::createError($e->getMessage(), 500);
        }
    }

    /**
     * @param array $item
     *
     * @throws Exception
     */
    private function validateBasketItem($item)
    {
        if (!isset($item['id'])) {
            $this->getLogger()->warning('Invalid id for basket item: '.json_encode($item));
            throw new Exception('ID puudub');
        }
        if (!isset($item['count']) || !ctype_digit($item['count']) || $item['count'] <= 0) {
            $this->getLogger()->warning('Invalid count for basket item: '.json_encode($item));
            throw new Exception('Vigane kogus '.$item['count']);
        }
        if (!isset($item['price'])) {
            $this->getLogger()->warning('Invalid price for basket item: '.json_encode($item));
            throw new Exception('Hind puudub');
        }
    }
}
