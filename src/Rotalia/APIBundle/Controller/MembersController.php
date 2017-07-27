<?php

namespace Rotalia\APIBundle\Controller;


use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\UserBundle\Model\Member;
use Rotalia\UserBundle\Model\MemberQuery;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class MembersController extends DefaultController
{
    /**
     * @param Request $request
     * @return JSendResponse
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *     },
     *     description="Fetch Members list",
     *     section="Members",
     *     filters={
     *          {"name"="name","type"="string"},
     *          {"name"="conventId","type"="int"},
     *          {"name"="offset","type"="int","default"="0"},
     *          {"name"="limit","type"="int","default"="10"},
     *     }
     * )
     */
    public function listAction(Request $request)
    {
        $memberQuery = MemberQuery::create();
        $name = $request->get('name');
        $conventId = $request->get('conventId');
        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 0);

        if ($limit) {
            $memberQuery
                ->limit($limit)
                ->offset($offset)
            ;
        }

        if (!empty($conventId)) {
            $memberQuery->filterByKoondisedId($conventId);
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
