<?php

namespace Rotalia\APIBundle\Controller;

use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Model\Product;
use Rotalia\InventoryBundle\Model\ProductQuery;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc; // Used for API documentation

/**
 * Class ProductsController
 * @package Rotalia\APIBundle\Controller
 */
class ProductsController extends DefaultController
{
    /**
     * Fetch list of product objects (id, name, price, unit). Supports pagination (page=1, limit=100).
     * Allows filtering active/inactive products, by name and productCode
     *
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *     },
     *     description="Fetch Product list",
     *     section="Products",
     *     filters={
     *          {"name"="name","type"="string"},
     *          {"name"="productCode","type"="string"},
     *          {"name"="productGroupId","type"="int"},
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
        $productGroupId = $request->get('productGroupId', null);

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

        if ($productGroupId !== null) {
            $productQuery->filterByProductGroupId($productGroupId);
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
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          400 = "Returned when ID is not valid",
     *          404 = "Returned when Product for ID is not found",
     *     },
     *     description="Fetch Product for ID",
     *     section="Products",
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
