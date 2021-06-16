<?php

namespace Rotalia\APIBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Rotalia\APIBundle\Form\CreditNettingRowType;
use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Form\FormErrorHelper;
use Rotalia\APIBundle\Model\CreditNetting;
use Rotalia\APIBundle\Model\CreditNettingQuery;
use Rotalia\APIBundle\Model\CreditNettingRow;
use Rotalia\APIBundle\Model\CreditNettingRowQuery;
use Rotalia\UserBundle\Model\User;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;


class CreditNettingsController extends DefaultController
{

    /**
     * Get list of CreditNettings. Only accessible by admins.
     *
     * @return JSendResponse
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          403 = "Returned when user has insufficient privileges",
     *     },
     *     description="Fetch CreditNettings list",
     *     section="CreditNettings",
     * )
     *
     * @param Request $request
     * @return JSendResponse
     */
    public function listAction(Request $request) {

        if ($this->isGranted(User::ROLE_ADMIN)) {
            $creditNettings = CreditNettingQuery::create()
                ->orderByCreatedAt(CRITERIA::DESC)
                ->find()
            ;
        } else {
            throw $this->createAccessDeniedException();
        }

        $result = [
            'creditNettings' => []
        ];

        /** @var CreditNetting $creditNetting */
        foreach ($creditNettings as $creditNetting) {
            $result['creditNettings'][] = $creditNetting->getAjaxData();
        }

        return JSendResponse::createSuccess($result);

    }

    /**
     * Update the state of CreditNettings. Only accessible by admins.
     *
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          400 = "Returned when there are errors with the submitted data",
     *          403 = "Returned when user has insufficient privileges",
     *          404 = "Returned when credit netting is not found"
     *     },
     *     description="Updates a CreditNetting",
     *     section="CreditNettings",
     *     input = "Rotalia\APIBundle\Form\CreditNettingRowType",
     *     filters={
     *          {"name"="conventId","type"="int","description"="Update creditNetting for another convent than member home convent"}
     *     }
     * )
     *
     * @param $id
     * @param Request $request
     * @return JSendResponse
     */
    public function patchAction(Request $request, $id) {

        $conventId = $request->get('conventId', null);

        if ($conventId === null) {
            $conventId = $this->getMember()->getKoondisedId();
        }

        if (!$this->isGranted(User::ROLE_ADMIN)) {
            return JSendResponse::createFail('Tasaarveldusi saab muuta ainult admin', 403);
        }

        if(!$this->isGranted(User::ROLE_SUPER_ADMIN) && $conventId != $this->getMember()->getKoondisedId()) {
            return JSendResponse::createFail('Teise konvendi tasaarveldusi saab muuta ainult super admin', 403);
        }

        if ($id === null) {
            return JSendResponse::createFail('Tasaarveldus peab olema määratud', 400);
        }

        $creditNettingRow = CreditNettingRowQuery::create()
            ->filterByConventId($conventId)
            ->filterByCreditNettingId($id)
            ->findOne()
        ;

        if ($creditNettingRow === null) {
            return JSendResponse::createFail('Tasaarveldust ei leitud', 404);
        }

        $form = $this->createForm(new CreditNettingRowType(), $creditNettingRow, [
            'csrf_protection' => false, // Disable for REST api
            'method' => $request->getMethod(),
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $creditNettingRow = $form->getData();
        } else {
            $errors = FormErrorHelper::getErrors($form);
            return JSendResponse::createFail('Tasaarvelduse muutmine ebaõnnestus', 400, $errors);
        }

        $creditNettingRow->save();

        return JSendResponse::createSuccess(['creditNetting' => $creditNettingRow->getCreditNetting()->getAjaxData()], [], 200);

    }

}