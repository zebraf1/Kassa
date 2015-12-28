<?php

namespace Rotalia\InventoryBundle\Controller;
use Rotalia\InventoryBundle\Model\Product;
use Rotalia\InventoryBundle\Model\ProductQuery;
use Rotalia\UserBundle\Model\Member;
use Rotalia\UserBundle\Model\MemberQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchController provides ajax search functionality for different objects
 *
 * @package Rotalia\InventoryBundle\Controller
 */
class SearchController extends DefaultController
{
    /**
     * Search members by name
     *
     * @param Request $request
     * @return string
     */
    public function memberAction(Request $request)
    {
        $name = $request->get('name');
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 100);

        // TODO: get selected convent id
        $conventId = 6; // Tallinn

        /** @var Member[] $members */
        $members = MemberQuery::create()
            ->filterByFullName('%'.$name.'%', \Criteria::LIKE)
            ->filterByKoondisedId($conventId)
            ->orderByEesnimi()
            ->orderByPerenimi()
            ->paginate($page, $limit)
        ;

        $resultArray = [
            'items' => [],
            'page' => $page,
        ];

        foreach ($members as $member) {
            $resultArray['items'][] = [
                'id' => $member->getId(),
                'text' => $member->getFullName(),
            ];
        }

        return JsonResponse::create($resultArray);
    }

    /**
     * Search products by name
     *
     * @param Request $request
     * @return string
     */
    public function productAction(Request $request)
    {
        $name = $request->get('name');
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 100);

        /** @var Product[] $members */
        $members = ProductQuery::create()
            ->filterByName('%'.$name.'%', \Criteria::LIKE)
            ->orderByName()
            ->paginate($page, $limit)
        ;

        $resultArray = [
            'items' => [],
            'page' => $page,
        ];

        foreach ($members as $member) {
            $resultArray['items'][] = [
                'id' => $member->getId(),
                'text' => $member->getName(),
            ];
        }

        return JsonResponse::create($resultArray);
    }
}
