<?php

namespace Rotalia\APIBundle\Controller;

use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\APIBundle\Form\FormErrorHelper;
use Rotalia\APIBundle\Form\ProductGroupType;
use Rotalia\APIBundle\Model\ProductGroup;
use Rotalia\APIBundle\Model\ProductGroupQuery;
use Nelmio\ApiDocBundle\Annotation\ApiDoc; // Used for API documentation
use Rotalia\UserBundle\Model\User;
use Symfony\Component\HttpFoundation\Request;

class ProductGroupsController extends DefaultController
{
    /**
     * Fetch all ProductGroup objects (id, name, seq), ordered by seq.
     *
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          403 = "Returned when user is not authenticated",
     *     },
     *     description="Fetch Product Group list",
     *     section="ProductGroups",
     * )
     *
     * @return JSendResponse
     */
    public function listAction()
    {
        /** @var ProductGroup[] $productGroups */
        $productGroups = ProductGroupQuery::create()
            ->orderBySeq()
            ->find()
        ;

        $result = [
            'productGroups' => []
        ];

        foreach ($productGroups as $productGroup) {
            $result['productGroups'][] = $productGroup->getAjaxData();
        }

        return JSendResponse::createSuccess($result);
    }

    /**
     * Creates a new ProductGroup
     *
     * @ApiDoc(
     *   resource = true,
     *   section="ProductGroups",
     *   description = "Creates a new product group from the submitted data.",
     *   input = "Rotalia\APIBundle\Form\ProductGroupType",
     *   statusCodes = {
     *     201 = "Returned when new product group is created. Includes created object",
     *     400 = "Returned when the form has errors",
     *     403 = "Returned when user has insufficient privileges",
     *   }
     * )
     * @param Request $request
     * @return JSendResponse
     */
    public function postAction(Request $request)
    {
        return $this->handleSubmit(new ProductGroup(), $request);
    }

    /**
     * Update product group data
     *
     * @ApiDoc(
     *   resource = true,
     *   section="ProductGroups",
     *   description = "Applies given attributes to the ProductGroup with the selected ID",
     *   input = "Rotalia\APIBundle\Form\ProductGroupType",
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
        $productGroup = ProductGroupQuery::create()->findPk($id);

        if ($productGroup === null) {
            return JSendResponse::createFail('Tootegruppi ei leitud', 404);
        }

        return $this->handleSubmit($productGroup, $request);
    }

    /**
     *
     * @param ProductGroup $productGroup
     * @param Request $request
     * @return JSendResponse
     */
    private function handleSubmit(ProductGroup $productGroup, Request $request)
    {
        if (!$this->isGranted(User::ROLE_ADMIN)) {
            return JSendResponse::createFail('Tegevus vajab admin õiguseid', 403);
        }


        $form = $this->createForm(new ProductGroupType(), $productGroup, [
            'csrf_protection' => false, // Disable for REST api
            'method' => $request->getMethod(),
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var ProductGroup $productGroup */
            $productGroup = $form->getData();
            $productGroup->save();
            $code = $request->getMethod() === 'POST' ? 201 : 200;
            return JSendResponse::createSuccess(['productGroup' => $productGroup->getAjaxData()], [], $code);
        } else {
            $errors = FormErrorHelper::getErrors($form);

            return JSendResponse::createFail('Tootegrupi salvestamine ebaõnnestus', 400, $errors);
        }
    }
}
