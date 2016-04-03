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
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 50);
        $sort = $request->get('sort');

        $pagerParams = [
            'page' => '__page__', //dynamically replaced by pagination component
        ];

        //TODO: support different convents
        $conventId = 6; //Tallinn

        /** @var MemberQuery $membersQuery */
        $membersQuery = MemberQuery::create()
            ->filterByKoondisedId($conventId)
            ->innerJoinMemberCredit('member_credit')
        ;

        // Sorting
        switch ($sort) {
            case 'name_asc':
                $membersQuery
                    ->orderByEesnimi()
                    ->orderByPerenimi()
                ;
                $pagerParams['sort'] = $sort;
                break;
            case 'name_desc':
                $membersQuery
                    ->orderByEesnimi(\Criteria::DESC)
                    ->orderByPerenimi(\Criteria::DESC)
                ;
                $pagerParams['sort'] = $sort;
                break;
            case 'credit_desc':
                $membersQuery
                    ->orderByMemberCredit(\Criteria::DESC)
                    ->orderByEesnimi()
                    ->orderByPerenimi()
                ;
                $pagerParams['sort'] = $sort;
                break;
            default: //credit asc
                $membersQuery
                    ->orderByMemberCredit()
                    ->orderByEesnimi()
                    ->orderByPerenimi()
                ;
                break;
        }

        /** @var Member[] $members */
        $members = $membersQuery->paginate($page, $limit);

        return $this->render('RotaliaInventoryBundle:Credit:list.html.twig', [
            'members' => $members,
            'pagerParams' => $pagerParams,
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
