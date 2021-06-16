<?php

namespace Rotalia\InventoryBundle\Controller;
use Rotalia\APIBundle\Model\Product;
use Rotalia\APIBundle\Model\ProductGroup;
use Rotalia\APIBundle\Model\ProductGroupQuery;
use Rotalia\APIBundle\Model\ProductQuery;
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

        /** @var Member[] $members */
        $members = MemberQuery::create()
            ->filterByFullName('%'.$name.'%', \Criteria::LIKE)
            ->filterByKoondisedId([6,7], \Criteria::IN) // Tallinn, Tartu
            ->filterByLahkPohjusedId(0) // only active members
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
        $active = $request->get('active', null);
        $conventId = $request->get('conventId', $this->getMember()->getConventId());

        /** @var Product[]|\PropelModelPager $products */
        $products = ProductQuery::create()
            ->filterByProductCode($name)
            ->orderByName()
            ->useProductInfoQuery('info', \Criteria::LEFT_JOIN)
                ->filterByActiveStatus($active)
                ->filterByConventId($conventId)
            ->endUse()
            ->paginate($page, $limit)
        ;

        if (!count($products)) {
            $products = ProductQuery::create()
                ->filterByName('%'.$name.'%', \Criteria::LIKE)
                ->orderByName()
                ->useProductInfoQuery('info', \Criteria::LEFT_JOIN)
                    ->filterByActiveStatus($active)
                    ->filterByConventId($conventId)
                ->endUse()
                ->paginate($page, $limit)
            ;
        }

        $resultArray = [
            'items' => [],
            'page' => $page,
            'hasNextPage' => !$products->isLastPage() && $products->count() > 0
        ];

        foreach ($products as $product) {
            $resultArray['items'][] = [
                'id' => $product->getId(),
                'text' => $product->getName(),
                'price' => $product->getPrice()
            ];
        }

        return JsonResponse::create($resultArray);
    }

    /**
     * Search product groups by name
     *
     * @param Request $request
     * @return string
     */
    public function productGroupAction(Request $request)
    {
        $name = $request->get('name');

        /** @var ProductGroup[] $productGroups */
        $productGroups = ProductGroupQuery::create()
            ->filterByName('%'.$name.'%', \Criteria::LIKE)
            ->orderBySeq()
            ->find()
        ;

        $resultArray = [
            'items' => [],
            'page' => 1,
            'hasNextPage' => false
        ];

        foreach ($productGroups as $productGroup) {
            $resultArray['items'][] = [
                'id' => $productGroup->getId(),
                'text' => $productGroup->getAjaxName(),
            ];
        }

        return JsonResponse::create($resultArray);
    }
}
