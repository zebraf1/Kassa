<?php

namespace Rotalia\APIBundle\Controller;


use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\UserBundle\Model\Member;
use Rotalia\UserBundle\Model\MemberQuery;
use Rotalia\UserBundle\Model\User;
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
     *          {"name"="name","type"="string","description"="Search by member name and last name. Searches from name start to allow partial matches"},
     *          {"name"="conventId","type"="int","description"="Returns only members of the given convent"},
     *          {"name"="isActive","type"="bool","description"="Returns active members when true or inactive ones when false. Returns both members when not provided"},
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
        $isActive = $request->get('isActive', null);
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

        if ($isActive !== null) {
            // Filter active or inactive members
            if (filter_var($isActive, FILTER_VALIDATE_BOOLEAN) === true) {
                $memberQuery->filterByLahkPohjusedId(0);
            } else {
                $memberQuery->filterByLahkPohjusedId(0, \Criteria::GREATER_THAN);
            }
        }

        /** @var Member[] $members */
        $members = $memberQuery
            ->orderByEesnimi()
            ->orderByPerenimi()
            ->find();


        $memberConventId = $this->getMember()->getConventId();
        $result = [];
        foreach ($members as $member) {
            // Show credit balance only for members of admins convent or for all, if super admin.
            $result[] = $member->getAjaxData(
                $this->isGranted(User::ROLE_SUPER_ADMIN) ||
                ($this->isGranted(User::ROLE_ADMIN) && $member->getConventId() == $memberConventId));
        }

        return JSendResponse::createSuccess(['members' => $result]);
    }
}
