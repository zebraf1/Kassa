<?php

namespace Rotalia\InventoryBundle\Controller;

use Rotalia\UserBundle\Model\Member;
use Rotalia\UserBundle\Model\MemberQuery;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CreditManagementController
 * @package Rotalia\InventoryBundle\Controller
 */
class CreditManagementController extends DefaultController
{
    public function listAction(Request $request)
    {
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
}
