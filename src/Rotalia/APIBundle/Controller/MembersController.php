<?php

namespace Rotalia\APIBundle\Controller;


use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\UserBundle\Model\Member;
use Rotalia\UserBundle\Model\MemberQuery;
use Symfony\Component\HttpFoundation\Request;

class MembersController extends DefaultController
{
    public function listAction(Request $request)
    {
        $memberQuery = MemberQuery::create();
        $name = $request->get('name');
        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 0);

        if ($limit) {
            $memberQuery
                ->limit($limit)
                ->offset($offset)
            ;
        }

        if (!empty($name)) {
            $memberQuery->filterByFullName($name.'%', \Criteria::LIKE);
        }

        /** @var Member[] $members */
        $members = $memberQuery
            ->orderByEesnimi()
            ->orderByPerenimi()
            ->find();


        $result = [];
        foreach ($members as $member) {
            $result[] = $member->getAjaxData();
        }

        return JSendResponse::createSuccess(['members' => $result]);
    }
}
