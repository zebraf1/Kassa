<?php

namespace Rotalia\APIBundle\Controller;


use Rotalia\APIBundle\Form\ReportType;
use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Form\FormErrorHelper;
use Rotalia\InventoryBundle\Model\Report;
use Rotalia\InventoryBundle\Model\ReportQuery;
use Rotalia\UserBundle\Model\User;
use Symfony\Component\HttpFoundation\Request;
use DateTime;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ReportsController extends DefaultController
{
    /**
     * Fetch list of Reports
     *
     * @param Request $request
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          400 = "Returned when dateFrom or dateUntil are not in valid date format",
     *          403 = "Returned when user is not authenticated",
     *     },
     *     description="Fetch Reports list",
     *     section="Reports",
     *     filters={
     *          {"name"="dateFrom","type"="string","description":"datetime string, default today 00:00"},
     *          {"name"="dateUntil","type"="string","description":"datetime string, default today 23:59"},
     *          {"name"="conventId","type"="int","description"="Fetch reports for another convent than member home convent"},
     *     }
     * )
     *
     * @param Request $request
     * @return JSendResponse
     */
    public function listAction(Request $request)
    {
        $dateFrom = $request->get('dateFrom', 'today 00:00');
        $dateUntil = $request->get('dateUntil', 'today 23:59');
        $conventId = $request->get('conventId', null);
        $memberConventId = $this->getMember()->getKoondisedId();

        if ($conventId === null) {
            $conventId = $this->getMember()->getKoondisedId();
        }

        if ($conventId === null) {
            $conventId = $memberConventId;
        }

        if ($conventId != $memberConventId && !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            return JSendResponse::createFail(['message' => 'Teise konvendi raporteid saab näha ainult super admin'], 403);
        }


        try {
            $from = new DateTime($dateFrom);
        } catch (\Exception $e) {
            return JSendResponse::createFail(['dateFrom' => $e->getMessage()], 400);
        }

        try {
            $until = new DateTime($dateUntil);
        } catch (\Exception $e) {
            return JSendResponse::createFail(['dateUntil' => $e->getMessage()], 400);
        }

        $reports = ReportQuery::create()
            ->filterByCreatedAt(['min' => $from, 'max' => $until])
            ->filterByConventId($conventId)
            ->orderByCreatedAt(\Criteria::DESC)
            ->leftJoinReportRow('reportRow')
            ->with('reportRow')
            ->find()
        ;

        $resultReports = [];

        foreach ($reports as $report) {
            $resultReports[] = $report->getAjaxData();
        }

        return JSendResponse::createSuccess(['reports' => $resultReports]);
    }

    /**
     * Create a new report. Use productId's as reportRows array keys to avoid unpredictable results when updating.
     * @ApiDoc(
     *   resource = true,
     *   section="Reports",
     *   description = "Creates a new Report with the given attributes",
     *   input = "Rotalia\APIBundle\Form\ReportType",
     *   filters={
     *      {"name"="conventId","type"="int","description"="Save report for selected convent instead of member home convent"},
     *      {"name"="type","type"="string","description"="Report type: VERIFICATION (default) or UPDATE"},
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
        $conventId = $request->get('conventId', null);
        $memberConventId = $this->getMember()->getKoondisedId();

        if ($conventId === null) {
            $conventId = $memberConventId;
        }

        if ($conventId != $memberConventId && !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            return JSendResponse::createFail(['message' => 'Tegevuseks pead olema super admin'], 403);
        }

        $type = $request->get('type', Report::TYPE_VERIFICATION);

        if (!in_array($type, Report::$types)) {
            return JSendResponse::createFail(['type' => 'Vigane raporti tüüp'], 400);
        }

        if ($type === Report::TYPE_UPDATE && $this->isGranted(User::ROLE_ADMIN) === false) {
            return JSendResponse::createFail(['type' => 'Sellist tüüpi raportit saab tekitada admin'], 403);
        }

        $report = new Report();
        $report->setConventId($conventId);
        $report->setMember($this->getMember());
        $report->setType($type);

        return $this->handleSubmit($request, $report);
    }

    /**
     * Update a report. Use productId's as reportRows array keys to avoid unpredictable results.
     *
     * @ApiDoc(
     *   resource = true,
     *   section="Reports",
     *   description = "Updates a Report with the given attributes",
     *   input = "Rotalia\APIBundle\Form\ReportType",
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     403 = "Returned when user has insufficient privileges",
     *     404 = "Returned when the Report is not found for ID",
     *   }
     * )
     * @param Request $request
     * @param $id
     * @return JSendResponse
     */
    public function updateAction(Request $request, $id)
    {
        $report = ReportQuery::create()->findPk($id);

        if ($report === null) {
            return JSendResponse::createFail(['message' => 'Aruannet ei leitud'], 404);
        }

        $conventId = $report->getConventId();
        $memberConventId = $this->getMember()->getKoondisedId();

        if ($conventId != $memberConventId && !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            return JSendResponse::createFail(['message' => 'Tegevuseks pead olema super admin'], 403);
        }

        if ($report->getMemberId() != $this->getMember()->getId() && !$this->isGranted(User::ROLE_ADMIN)) {
            return JSendResponse::createFail(['message' => 'Tegevuseks pead olema admin või raporti looja'], 403);
        }

        return $this->handleSubmit($request, $report);
    }


    /**
     * @param Request $request
     * @param Report $report
     * @return JSendResponse
     */
    protected function handleSubmit(Request $request, Report $report)
    {
        if (!$this->isGranted(User::ROLE_USER)) {
            return JSendResponse::createFail(['message' => 'Tegevuseks pead olema sisse logitud'], 403);
        }

        $form = $this->createForm(new ReportType(), $report, [
            'method' => $request->getMethod(),
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var Report $report */
            $report = $form->getData();
            $report->save();
            $code = $request->getMethod() === 'POST' ? 201 : 200;
            return JSendResponse::createSuccess(['reportId' => $report->getId()], [], $code);
        } else {
            $errors = FormErrorHelper::getErrors($form);

            return JSendResponse::createFail([
                'message' => 'Aruande salvestamine ebaõnnestus',
                'errors' => $errors
            ], 400);
        }
    }
}
