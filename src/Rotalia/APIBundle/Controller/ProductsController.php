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
     *          403 = "Returned when user is not authenticated",
     *     },
     *     description="Fetch Product list",
     *     section="Products",
     *     filters={
     *          {"name"="name","type"="string"},
     *          {"name"="productCode","type"="string"},
     *          {"name"="productGroupId","type"="int"},
     *          {"name"="page","type"="int","default"="1"},
     *          {"name"="limit","type"="int","default"="0"},
     *          {"name"="active","type"="boolean"},
     *          {"name"="conventId","type"="int","description"="Fetch price and status for another convent than member home convent"},
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
        $limit = (int)$request->get('limit', 0);
        $active = $request->get('active', null);
        $productGroupId = $request->get('productGroupId', null);

        $conventId = $request->get('conventId', null);

        if ($conventId === null) {
            $conventId = $this->getMember()->getKoondisedId();
        }

        if ($active !== null) {
            $active = filter_var($active, FILTER_VALIDATE_BOOLEAN);
        }

        /** @var Product[]|\PropelModelPager $products */
        $productQuery = ProductQuery::create()
            ->orderByName()
        ;

        $productQuery
            ->useProductInfoQuery('info', \Criteria::LEFT_JOIN)
                ->filterByActiveStatus($active)
            ->endUse()
            ->useProductInfoQuery('info', \Criteria::LEFT_JOIN)
            ->filterByConventId($conventId)
                ->_or()
                ->filterByConventId(null, \Criteria::ISNULL)
            ->endUse()
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
            $product->setConventId($conventId);
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
     *     filters={
     *          {"name"="conventId","type"="int","description"="Fetch price and status for another convent than member home convent"},
     *     }
     * )
     *
     * @param Request $request
     * @param $id
     * @return JSendResponse
     */
    public function getAction(Request $request, $id)
    {
        if (empty($id)) {
            return JSendResponse::createFail('ID puudub', 400);
        }

        $conventId = $request->get('conventId', null);

        if ($conventId === null) {
            $conventId = $this->getMember()->getKoondisedId();
        }

        $product = ProductQuery::create()->findPk($id);

        if ($product === null) {
            return JSendResponse::createFail('Toodet ei leitud', 404);
        }

        $product->setConventId($conventId);

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
     *   filters={
     *      {"name"="conventId","type"="int","description"="Set price and status for selected convent instead of member home convent"},
     *   },
     *   statusCodes = {
     *     201 = "Returned when new product is created. Includes created object",
     *     400 = "Returned when the form has errors",
     *     403 = "Returned when user has insufficient privileges",
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
     *   filters={
     *      {"name"="conventId","type"="int","description"="Set price and status for selected convent instead of member home convent"},
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     403 = "Returned when user has insufficient privileges",
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
            return JSendResponse::createFail('Tegevus vajab admin õiguseid', 403);
        }

        $conventId = $request->get('conventId', null);
        $memberConventId = $this->getMember()->getKoondisedId();

        if ($conventId === null) {
            $conventId = $memberConventId;
        }

        if ($conventId != $memberConventId && !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            return JSendResponse::createFail('Teise konvendi tooteid saab muuta ainult super admin', 403);
        }

        $product->setConventId($conventId);

        $form = $this->createForm(new ProductType(), $product, [
            'csrf_protection' => false, // Disable for REST api
            'method' => $request->getMethod(),
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var Product $product */
            $product = $form->getData();

            $product->ensureProductInfos();

            $product->save();
            $code = $request->getMethod() === 'POST' ? 201 : 200;
            return JSendResponse::createSuccess(['product' => $product->getAjaxData()], [], $code);
        } else {
            $errors = FormErrorHelper::getErrors($form);

            return JSendResponse::createFail([
                'message' => 'Toote salvestamine ebaõnnestus',
                'errors' => $errors
            ], 400);
        }
    }
}
