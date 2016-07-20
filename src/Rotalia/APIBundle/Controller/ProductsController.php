<?php

namespace Rotalia\APIBundle\Controller;

use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Form\FormErrorHelper;
use Rotalia\InventoryBundle\Form\ProductType;
use Rotalia\InventoryBundle\Model\Product;
use Rotalia\InventoryBundle\Model\ProductQuery;
use Rotalia\UserBundle\Model\User;
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
     * Creates a new Product
     *
     * @ApiDoc(
     *   resource = true,
     *   section="Products",
     *   description = "Creates a new product from the submitted data.",
     *   input = "Rotalia\InventoryBundle\Form\ProductType",
     *   statusCodes = {
     *     201 = "Returned when new product is created. Includes created object",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     * @param Request $request
     * @return JSendResponse
     */
    public function postAction(Request $request)
    {
        return $this->handleSubmit(new Product(), $request);
    }

    /**
     * Update product data
     *
     * @ApiDoc(
     *   resource = true,
     *   section="Products",
     *   description = "Applies given attributes to the Product with the selected ID",
     *   input = "Rotalia\InventoryBundle\Form\ProductType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     404 = "Returned when the Product is not found for ID",
     *   }
     * )
     * @param Request $request
     * @return JSendResponse
     */
    public function patchAction(Request $request, $id)
    {
        $product = ProductQuery::create()->findPk($id);

        if ($product === null) {
            return JSendResponse::createFail('Toodet ei leitud', 404);
        }

        return $this->handleSubmit($product, $request);
    }

    /**
     *
     * @param Product $product
     * @param Request $request
     * @return JSendResponse
     */
    private function handleSubmit(Product $product, Request $request)
    {
        if (!$this->isGranted(User::ROLE_ADMIN)) {
            return JSendResponse::createFail('Access Denied', 403);
        }

        $form = $this->createForm(new ProductType(), $product, [
            'csrf_protection' => false, // Disable for REST api
            'method' => $request->getMethod(),
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $product = $form->getData();
            $product->save();
            $code = $request->getMethod() === 'POST' ? 201 : 200;
            return JSendResponse::createSuccess(['product' => $product->getAjaxData()], [], $code);
        } else {
            $errors = FormErrorHelper::getErrors($form);

            return JSendResponse::createFail([
                'message' => 'Toote lisamine ebaõnnestus',
                'errors' => $errors
            ], 400);
        }
    }
}
