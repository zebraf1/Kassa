<?php

namespace Rotalia\APIBundle\Controller;

use Rotalia\APIBundle\Form\PointOfSaleType;
use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\APIBundle\Form\FormErrorHelper;
use Rotalia\APIBundle\Model\PointOfSale;
use Rotalia\APIBundle\Model\PointOfSaleQuery;
use Rotalia\UserBundle\Model\User;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PointOfSalesController
 * @package Rotalia\APIBundle\Controller
 */
class PointOfSalesController extends DefaultController
{
    /**
     * Get list of PointOfSales. Super admin will get all, admin will get PointOfSales for his convent.
     *
     * @return JSendResponse
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          403 = "Returned when user does not have admin role",
     *     },
     *     description="Fetch PointOfSales list",
     *     section="PointOfSales",
     * )
     */
    public function listAction()
    {
        if ($this->isGranted(User::ROLE_SUPER_ADMIN)) {
            $pointOfSales = PointOfSaleQuery::create()
                ->leftJoinMember('member')
                ->with('member')
                ->find()
            ;
        } elseif ($this->isGranted(User::ROLE_ADMIN)) {
            $pointOfSales = PointOfSaleQuery::create()
                ->filterByConventId($this->getMember()->getKoondisedId())
                ->leftJoinMember('member')
                ->with('member')
                ->find()
            ;
        } else {
            throw $this->createAccessDeniedException();
        }

        $result = [
            'pointOfSales' => []
        ];

        /** @var PointOfSale $pointOfSale */
        foreach ($pointOfSales as $pointOfSale) {
            $result['pointOfSales'][] = $pointOfSale->getAjaxData();
        }

        return JSendResponse::createSuccess($result);
    }

    /**
     * Get info of a PointOfSale
     *
     * @param $id
     * @return JSendResponse
     * @ApiDoc(
     *   resource = true,
     *   section="PointOfSales",
     *   description = "Find PointOfSale for the given ID",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     403 = "Returned when user has insufficient privileges",
     *     404 = "Returned when the PointOfSale is not found for ID",
     *   }
     * )
     */
    public function getAction($id)
    {
        $pointOfSale = PointOfSaleQuery::create()
            ->leftJoinMember('member')
            ->with('member')
            ->findPk($id)
        ;

        if ($pointOfSale === null) {
            return JSendResponse::createFail('Müügikohta ei leitud', 404);
        }

        return JSendResponse::createSuccess(['pointOfSale' => $pointOfSale->getAjaxData()]);
    }

    /**
     * Creates a new PointOfSale
     *
     * @ApiDoc(
     *   resource = true,
     *   section="PointOfSales",
     *   description = "Creates a new PointOfSale from the submitted data.",
     *   input = "Rotalia\APIBundle\Form\PointOfSaleType",
     *   filters={
     *      {"name"="conventId","type"="int","description"="Create for selected convent instead of member home convent"},
     *   },
     *   statusCodes = {
     *     201 = "Returned when new PointOfSale is created. Includes created object",
     *     400 = "Returned when the current browser is already a point of sale",
     *     403 = "Returned when user has insufficient privileges",
     *   }
     * )
     * @param Request $request
     * @return JSendResponse
     */
    public function postAction(Request $request)
    {
        $hash = $request->cookies->get('pos_hash');

        if ($hash && $pos = PointOfSaleQuery::create()->findOneByHash($hash)) {
             return JSendResponse::createFail('See brauser on juba müügipunkt', 400);
        }

        $conventId = $request->get('conventId', null);
        $memberConventId = $this->getMember()->getKoondisedId();

        if ($this->isGranted(User::ROLE_SUPER_ADMIN) === false && $conventId != $memberConventId) {
            return JSendResponse::createFail('Õigused puuduvad, vajab super admin õigusi', 403);
        }

        $hash = md5($request->getClientIp() . time());
        $pos = new PointOfSale();
        $pos->setMember($this->getMember());
        $pos->setHash($hash);
        $pos->setDeviceInfo($request->headers->get('User-Agent'));
        $pos->setCreatedAt(new \DateTime());
        $pos->setConventId($conventId);

        $response = $this->handleSubmit($pos, $request);
        $response->headers->setCookie(new Cookie('pos_hash', $pos->getHash(), new \DateTime('+1 year')));

        return $response;
    }

    /**
     * Updates a PointOfSale
     *
     * @ApiDoc(
     *   resource = true,
     *   section="PointOfSales",
     *   description = "Update PointOfSale from the submitted data.",
     *   input = "Rotalia\APIBundle\Form\PointOfSaleType",
     *   statusCodes = {
     *     200 = "Returned when new PointOfSale is created. Includes created object",
     *     400 = "Returned when there are errors with the submitted data",
     *     403 = "Returned when user has insufficient privileges",
     *   }
     * )
     * @param Request $request
     * @return JSendResponse
     */
    public function patchAction($id, Request $request)
    {
        $pos = PointOfSaleQuery::create()->findPk($id);
        if ($pos === null) {
            return JSendResponse::createFail('Müügipunkti ei leitud', 404);
        }

        if ($this->isGranted(User::ROLE_ADMIN) === false) {
            return JSendResponse::createFail('Õigused puuduvad, vajab admin õigusi', 403);
        }

        $memberConventId = $this->getMember()->getKoondisedId();

        if ($this->isGranted(User::ROLE_SUPER_ADMIN) === false && $pos->getConventId() != $memberConventId) {
            return JSendResponse::createFail('Õigused puuduvad, vajab super admin õigusi', 403);
        }

        return $this->handleSubmit($pos, $request);
    }

    /**
     * Deletes a PointOfSale
     *
     * @ApiDoc(
     *   resource = true,
     *   section="PointOfSales",
     *   description = "Deletes a PointOfSale with the given ID",
     *   statusCodes = {
     *     200 = "Returned when the PointOfSale is deleted",
     *     403 = "Returned when user has insufficient privileges",
     *     404 = "Returned when PointOfSale is not found for the given ID",
     *   }
     * )
     * @param $id
     * @return JSendResponse
     */
    public function deleteAction($id)
    {
        $pos = PointOfSaleQuery::create()->findPk($id);
        if ($pos === null) {
            return JSendResponse::createFail('Müügipunkti ei leitud', 404);
        }

        if ($this->isGranted(User::ROLE_ADMIN) === false) {
            return JSendResponse::createFail('Õigused puuduvad, vajab admin õigusi', 403);
        }

        $memberConventId = $this->getMember()->getKoondisedId();

        if ($this->isGranted(User::ROLE_SUPER_ADMIN) === false && $pos->getConventId() != $memberConventId) {
            return JSendResponse::createFail('Õigused puuduvad, vajab super admin õigusi', 403);
        }

        $pos->delete();

        return JSendResponse::createSuccess(['message' => 'Müügipunkt kustutatud']);
    }

    /**
     * @param PointOfSale $pos
     * @param Request $request
     * @return JSendResponse
     */
    private function handleSubmit(PointOfSale $pos, Request $request)
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
            return JSendResponse::createFail('Teise konvendi müügikohtasid saab hallata ainult super admin', 403);
        }

        $pos->setConventId($conventId);

        $form = $this->createForm(new PointOfSaleType(), $pos, [
            'csrf_protection' => false, // Disable for REST api
            'method' => $request->getMethod(),
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var PointOfSale $pos */
            $pos = $form->getData();

            $pos->save();
            $code = $request->getMethod() === 'POST' ? 201 : 200;
            //Cookie should be refreshed every time it is used

            return JSendResponse::createSuccess(['pointOfSale' => $pos->getAjaxData()], [], $code);
        } else {
            $errors = FormErrorHelper::getErrors($form);

            return JSendResponse::createFail('Müügikoha salvestamine ebaõnnestus', 400, $errors);
        }
    }
}
