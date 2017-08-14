<?php

namespace Rotalia\APIBundle\Controller;

use Exception;
use Rotalia\APIBundle\Form\TransferType;
use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Form\FormErrorHelper;
use Rotalia\InventoryBundle\Model\Transfer;
use Rotalia\InventoryBundle\Model\TransferPeer;
use Rotalia\InventoryBundle\Model\TransferQuery;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Rotalia\UserBundle\Model\MemberQuery;
use Rotalia\UserBundle\Model\User;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

class TransfersController extends DefaultController
{

    /**
     * Fetch list of transfers
     *
     * @param Request $request
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          400 = "Returned when dateFrom or dateUntil are not in valid date format or both conventId and memberId filters are null",
     *          403 = "Returned when user has insufficient privileges",
     *     },
     *     description="Fetch transfers list",
     *     section="Transfers",
     *     filters={
     *          {"name"="conventId","type"="int","description"="Fetch reports for another convent than member home convent"},
     *          {"name"="memberId","dataType"="integer","description"="Member ID of user in interest"},
     *          {"name"="dateFrom","type"="string","description":"datetime string"},
     *          {"name"="dateUntil","type"="string","description":"datetime string"},
     *          {"name"="limit","type"="int","description":"Limit number of transfers returned, default 5"},
     *          {"name"="offset","type"="int","description":"Offset transfers returned for pagination, default 0"},
     *     }
     * )
     *
     * @param Request $request
     * @return JSendResponse
     */
    public function listAction(Request $request)
    {
        $conventId = $request->get('conventId', null);
        $memberId = $request->get('memberId', null);
        $dateFrom = $request->get('dateFrom', null);
        $dateUntil = $request->get('dateUntil', null);
        $limit = $request->get('limit', 5);
        $offset = $request->get('offset', 0);

        $transferQuery = TransferQuery::create();

        $memberConventId = $this->getMember()->getKoondisedId();

        if ($conventId === null && $memberId === null && !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            return JSendResponse::createFail(['Täpsustada konvent või kasutaja'], 400);
        }

        if ($memberId !== null) {

            if ($memberId == $this->getMember()->getId()) {
                // User requests its own transfers
                $transferQuery->filterByMemberId($memberId);

            } else {
                return JSendResponse::createFail(['Pärida saab ainult enda ülekandeid'], 400);

            }

        }


        if ($conventId !== null) {

            if (($conventId == $memberConventId) && !$this->isGranted(User::ROLE_ADMIN)) {
                return JSendResponse::createFail(['Ainult admin saab vaadata kõiki konvendi ülekandeid'], 400);
            }

            if (($conventId != $memberConventId) && !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
                return JSendResponse::createFail(['Teiste konventide ülekandeid saab vaadata ainult super admin'], 400);
            }

            $transferQuery->filterByConventId($conventId);

        }

        if (!empty($dateFrom)) {
            try {
                $from = new DateTime($dateFrom);
                $transferQuery->filterByCreatedAt(['min' => $from]);
            } catch (\Exception $e) {
                return JSendResponse::createFail(['dateFrom' => $e->getMessage()], 400);
            }
        }

        if (!empty($dateUntil)) {
            try {
                $until = new DateTime($dateUntil);
                //Makes sure that the end date is included in the filter.
                $until->modify('+1 day');
                $transferQuery->filterByCreatedAt(['max' => $until]);
            } catch (\Exception $e) {
                return JSendResponse::createFail(['dateUntil' => $e->getMessage()], 400);
            }
        }

        $transfers = $transferQuery
            ->orderByCreatedAt(\Criteria::DESC)
            ->limit($limit)
            ->offset($offset)
            ->find()
        ;

        $resultTransfers = [];

        foreach ($transfers as $transfer) {
            $resultTransfers[] = $transfer->getAjaxData();
        }

        return JSendResponse::createSuccess(['transfers' => $resultTransfers]);

    }

    /**
     * Create a new transfer.
     * @ApiDoc(
     *   resource = true,
     *   section="Transfers",
     *   description = "Creates a new transfer with the given attributes",
     *   input = "Rotalia\APIBundle\Form\TransferType",
     *   filters={
     *      {"name"="conventId","type"="int","description"="Save transfer for selected convent instead of member home convent"}
     *   },
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     403 = "Returned when user has insufficient privileges",
     *   }
     * )
     * @param Request $request
     * @return JSendResponse
     */
    public function createAction(Request $request)
    {

        if (!$this->isGranted(User::ROLE_ADMIN)) {
            return JSendResponse::createFail(['Ülekandeid saab lisada ainult admin'], 403);
        }

        $conventId = $request->get('conventId', null);

        $memberConventId = $this->getMember()->getKoondisedId();

        if ($conventId === null) {
            $conventId = $memberConventId;
        }

        if ($conventId !== $memberConventId && !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            return JSendResponse::createFail(['Teise konnventi ülekande lisamiseks peab olema super admin'], 403);
        }

        $connection = \Propel::getConnection(TransferPeer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
        $connection->beginTransaction();

        try {

            $transfer = new Transfer();
            $transfer->setConventId($conventId);
            $transfer->setCreatedAt(new \DateTime());
            $transfer->setCreatedBy($this->getMember()->getId());

            $form = $this->createForm(new TransferType(), $transfer, [
                'csrf_protection' => false, // Disable for REST api
                'method' => $request->getMethod(),
            ]);

            $form->handleRequest($request);

            if ($form->isValid()) {
                $transfer = $form->getData();
            } else {
                $errors = FormErrorHelper::getErrors($form);
                return JSendResponse::createFail('Ülekande salvestamine ebaõnnestus', 400, $errors);
            }

            $memberId = $transfer->getMemberId();

            if ($memberId) {
                $member = MemberQuery::create()->findPk($memberId);
                if ($member === null) {
                    return JSendResponse::createFail('Kasutajat ei leitud', 400);
                }
            } else {
                return JSendResponse::createFail('Kasutajat ei sisestatud', 400);
            }

            if ($transfer->getSum() == 0) {
                return JSendResponse::createFail('Summa peab olema nullist erinev', 400);
            }

            $transfer->save($connection);

            $memberCredit = $member->getCredit();
            $memberCredit->adjustCredit($transfer->getSum());
            $memberCredit->save($connection);

            $connection->commit();

            return JSendResponse::createSuccess(['transfer' => $transfer->getAjaxData()]);


        } catch (Exception $e) {
            $connection->rollBack();
            return JSendResponse::createError($e->getMessage(), 500);
        }
    }
}
