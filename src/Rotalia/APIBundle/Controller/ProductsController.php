<?php

namespace Rotalia\APIBundle\Controller;

use Rotalia\APIBundle\Form\ProductType;
use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\APIBundle\Form\FormErrorHelper;
use Rotalia\APIBundle\Model\Product;
use Rotalia\APIBundle\Model\ProductQuery;
use Rotalia\UserBundle\Model\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProductsController
 * @package Rotalia\APIBundle\Controller
 */
class ProductsController extends DefaultController
{
    /**
     * Finds a Product for the given ID. Returns a product object (id, name, price) or error 404 when product is not found.
     *
     * #ApiDoc(
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
     * #ApiDoc(
     *   resource = true,
     *   section="Products",
     *   description = "Creates a new product from the submitted data.",
     *   input = "Rotalia\APIBundle\Form\ProductType",
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
     * #ApiDoc(
     *   resource = true,
     *   section="Products",
     *   description = "Applies given attributes to the Product with the selected ID",
     *   input = "Rotalia\APIBundle\Form\ProductType",
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
        ])->add('productGroupId', 'text', [
            'label' => 'Toote grupp',
            'required' => false,
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

            return JSendResponse::createFail('Toote salvestamine ebaõnnestus', 400, $errors);
        }
    }
}
