<?php

namespace Rotalia\InventoryBundle\Controller;

use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Model\Transaction;
use Rotalia\UserBundle\Model\Member;
use Rotalia\UserBundle\Model\MemberCreditQuery;
use Rotalia\UserBundle\Model\MemberQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class CreditManagementController
 * @package Rotalia\InventoryBundle\Controller
 */
class CreditManagementController extends DefaultController
{
    public function listAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 100);

        //TODO: support different convents
        $conventId = 6; //Tallinn

        /** @var Member[] $members */
        $members = MemberQuery::create()
            ->filterByKoondisedId($conventId)
            ->joinMemberCredit()
            ->useMemberCreditQuery()
                ->orderByCredit()
            ->endUse()
            ->paginate($page, $limit)
        ;

        return $this->render('RotaliaInventoryBundle:Credit:list.html.twig', [
            'members' => $members,
        ]);
    }

    /**
     * Adjust members credit
     *
     * @param Request $request
     * @return JSendResponse
     */
    public function adjustAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return JSendResponse::createFail('Selleks pead olema sisse logitud admin õigustes', 403);
        }

        $memberId = $request->get('memberId');
        $amount = $request->get('amount');

        if (!$memberId) {
            return JSendResponse::createFail('Vigane parameeter memberId', 400);
        }
        if (!$amount) {
            return JSendResponse::createFail('Vigane parameeter amount', 400);
        }

        $member = MemberQuery::create()->findPk($memberId);

        if ($member === null) {
            return JSendResponse::createFail('Liiget ei leitud', 404);
        }

        $amount = doubleval($amount);

        if ($amount == 0) {
            return JSendResponse::createFail('Vigane summa', 400);
        }

        $con = \Propel::getConnection();
        $con->beginTransaction();
        try {
            //Create transaction
            $tx = new Transaction();
            $tx
                ->setMemberRelatedByCreatedBy($this->getMember())
                ->setMemberRelatedByMemberId($member)
                ->setSum($amount)
                ->setType(Transaction::TYPE_CREDIT_PAYMENT)
                ->save()
            ;

            $credit = MemberCreditQuery::create()
                ->filterByMemberId($member->getId())
                ->findOneOrCreate($con)
            ;

            $credit->adjustCredit($amount);
            $credit->save($con);

            $con->commit();

            $newCredit = number_format($credit->getCredit(), 2) . '€';
            $creditClass = 'credit-'.$credit->getCreditStatus();
            return JSendResponse::createSuccess([
                'newCredit' => $newCredit,
                'creditClass' => $creditClass,
            ]);
        } catch (\Exception $e) {
            $con->rollBack();
            return JSendResponse::createError('Tekkis viga:'.$e->getMessage(), 500);
        }
    }
}
