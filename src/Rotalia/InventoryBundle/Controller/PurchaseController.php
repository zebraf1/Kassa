<?php

namespace Rotalia\InventoryBundle\Controller;
use Exception;
use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Form\ProductFilterType;
use Rotalia\InventoryBundle\Model\ProductQuery;
use Rotalia\InventoryBundle\Model\Transaction;
use Rotalia\InventoryBundle\Model\TransactionPeer;
use Rotalia\UserBundle\Model\User;
use Rotalia\UserBundle\Model\UserQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
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
     *   description = "Creates transactions, reduces member credit and product storage amount",
     *   requirements={
     *     {"name"="payment","requirement"="cash|credit|refund","description"="Payment type"}
     *   },
     *   parameters={
     *      {"name"="username","dataType"="string","required"=false,"description"="Temporary login username"},
     *      {"name"="password","dataType"="string","required"=false,"description"="Temporary login password"},
     *      {"name"="basket","dataType"="Object","required"=false,"description"="Not required for refund payment"},
     *      {"name"="basket[0][id]","dataType"="int","required"=true,"description"="Product ID"},
     *      {"name"="basket[0][amount]","dataType"="float","required"=true,"description"="Amount purchased"},
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when input data has errors",
     *     403 = "Returned when user is not logged in and browser is not point of sale",
     *     500 = "Returned when the transaction fails, all actions are reverted. Try again or report a problem",
     *   }
     * )
     * @param Request $request
     * @param $payment
     * @param null $username
     * @param null $password
     * @return JSendResponse
     */
    public function paymentAction(Request $request, $payment, $username = null, $password = null)
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

        $basket = $request->get('basket');

        if ($payment !== 'refund') {
            if ($basket === null) {
                return JSendResponse::createFail('Ostukorv puudub', 400);
            }
            if (!is_array($basket) && $payment !== 'refund') {
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
        if ($username) {
            $user = UserQuery::create()->findOneByUsername($username);
            if ($user === null) {
                return JSendResponse::createFail('Kasutajat ei leitud', 400);
            }

            // Get the encoder for the users password
            /** @var EncoderFactoryInterface $encoderService */
            $encoderService = $this->get('security.encoder_factory');
            $encoder = $encoderService->getEncoder($user);

            // Note the difference
            if (!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
                // Get profile list
                return JSendResponse::createFail('Vale parool', 400);
            }

            $member = $user->getMember();
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
            $conventId = $member->getKoondisedId();
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
                $transaction->setPointOfSale($pos);
                $transaction->setType($transactionType);
                $transaction->save($connection);
                // credit balance changes the same way (positive cash = positive credit). This sum is deducted from balance
                $totalSumCents = (int)(100 * -$sum);
            } else {
                // Create transactions for all purchases
                foreach ($basket as $item) {
                    if (!$this->validateBasketItem($item)) {
                        return JSendResponse::createError('Ostukorvis on vigane toode: '.json_encode($item), 400);
                    }

                    $transaction = new Transaction();
                    $product = ProductQuery::create()->findPk($item['id']);
                    $product->setConventId($conventId);
                    $transaction->setAmount($item['amount']);
                    $transaction->setProduct($product);
                    $transaction->setCurrentPrice($product->getPrice());
                    $transaction->setMemberRelatedByCreatedBy($currentMember);
                    $transaction->setMemberRelatedByMemberId($member);
                    $transaction->setPointOfSale($pos);
                    $totalSumCents += (int)(100 * $transaction->calculateSum());
                    $transaction->setType($transactionType);
                    $transaction->save($connection);

                    $product->getProductInfo()->reduceStorageAmount($item['amount']);
                    $product->save();
                }
            }

            // Reduce member credit
            if ($payment !== 'cash') {
                $memberCredit = $member->getCredit();
                $memberCredit->adjustCredit(-$totalSumCents / 100);
                $memberCredit->save($connection);
            }

            $connection->commit();

            return JSendResponse::createSuccess([
                'totalSumCents' => $totalSumCents,
                'newCredit' => !empty($memberCredit) ? $memberCredit->getCredit() : null
            ]);
        } catch (Exception $e) {
            $connection->rollBack();
            return JSendResponse::createError($e->getMessage(), 500);
        }
    }

    /**
     * @param array $item
     * @return bool
     */
    private function validateBasketItem($item)
    {
        if (!isset($item['id'])) {
            $this->getLogger()->warning('Invalid id for basket item: '.json_encode($item));
            return false;
        }

        if (!isset($item['amount']) || !ctype_digit($item['amount'])) {
            $this->getLogger()->warning('Invalid amount for basket item: '.json_encode($item));
            return false;
        }

        if (!isset($item['price'])) {
            $this->getLogger()->warning('Invalid price for basket item: '.json_encode($item));
            return false;
        }

        return true;
    }
}
