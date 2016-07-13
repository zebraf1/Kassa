<?php

namespace Rotalia\APIBundle\Controller;

use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Model\Product;
use Rotalia\InventoryBundle\Model\ProductQuery;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc; // Used for API documentation

/**
 * Class ProductController
 * @package Rotalia\APIBundle\Controller
 */
class ProductController extends DefaultController
{
    /**
     * Fetch list of product objects (id, name, price). Supports pagination (page=1, limit=100).
     * Allows filtering active/inactive products, by name and productCode
     *
     * @ApiDoc(
     *     description="Fetch Product list",
     *     section="Product",
     *     filters={
     *          {"name"="name","type"="string"},
     *          {"name"="productCode","type"="string"},
     *          {"name"="page","type"="int","default"="1"},
     *          {"name"="limit","type"="int","default"="100"},
     *          {"name"="active","type"="boolean"},
     *     }
     * )
     *
     * @param Request $request
     * @return JSendResponse
     */
    public function listAction(Request $request)
    {
        $name = $request->get('name', null);
        $productCode = $request->get('productCode', null);
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 100);
        $active = $request->get('active', null);

        if ($active !== null) {
            $active = filter_var($active, FILTER_VALIDATE_BOOLEAN);
        }

        /** @var Product[]|\PropelModelPager $products */
        $productQuery = ProductQuery::create()
            ->filterByActiveStatus($active)
            ->orderByName()
        ;

        if ($name !== null) {
            $productQuery->filterByName('%'.$name.'%', \Criteria::LIKE);
        }

        if ($productCode !== null) {
            $productQuery->filterByProductCode($productCode);
        }

        $products = $productQuery
            ->paginate($page, $limit)
        ;

        $resultArray = [
            'products' => [],
            'page' => $page,
            'pages' => $products->getLastPage()
        ];

        foreach ($products as $product) {
            $resultArray['products'][] = $product->getAjaxData();
        }

        return JSendResponse::createSuccess($resultArray);
    }

    /**
     * Finds a Product for the given ID. Returns a product object (id, name, price) or error 404 when product is not found.
     *
     * @ApiDoc(
     *     description="Fetch Product for ID",
     *     section="Product",
     * )
     *
     * @param $id
     * @return JSendResponse
     */
    public function getAction($id)
    {
        if (empty($id)) {
            return JSendResponse::createFail('ID puudub', 400);
        }

        $product = ProductQuery::create()->findPk($id);

        if ($product === null) {
            return JSendResponse::createFail('Toodet ei leitud', 404);
        }

        return JSendResponse::createSuccess([
            'product' => $product->getAjaxData(),
        ]);
    }

    /**
     * CRSF token ID for current controller
     *
     * @return string
     */
    protected function getTokenId()
    {
        return 'restApiProductToken';
    }
}
