<?php

namespace Rotalia\APIBundle\Controller;


use Rotalia\APIBundle\Form\ReportType;
use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Form\FormErrorHelper;
use Rotalia\InventoryBundle\Model\Product;
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
     *          {"name"="dateFrom","type"="string","description":"datetime string"},
     *          {"name"="dateUntil","type"="string","description":"datetime string"},
     *          {"name"="limit","type"="int","description":"Limit number of reports returned, default 5"},
     *          {"name"="offset","type"="int","description":"Offset reports returned for pagination, default 0"},
     *          {"name"="conventId","type"="int","description"="Fetch reports for another convent than member home convent"},
     *     }
     * )
     *
     * @param Request $request
     * @return JSendResponse
     */
    public function listAction(Request $request)
    {
        $dateFrom = $request->get('dateFrom', null);
        $dateUntil = $request->get('dateUntil', null);
        $conventId = $request->get('conventId', null);
        $reportType = $request->get('reportType', null);
        $limit = $request->get('limit', 5);
        $offset = $request->get('offset', 0);

        $reportQuery = ReportQuery::create();

        $memberConventId = $this->getMember()->getKoondisedId();

        if ($conventId === null) {
            $conventId = $this->getMember()->getKoondisedId();
        }

        if ($conventId === null) {
            $conventId = $memberConventId;
        }

        if ($conventId != $memberConventId && !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            return JSendResponse::createFail('Teise konvendi raporteid saab näha ainult super admin', 403);
        }


        if (!empty($dateFrom)) {
            try {
                $from = new DateTime($dateFrom);
                $reportQuery->filterByCreatedAt(['min' => $from]);
            } catch (\Exception $e) {
                return JSendResponse::createFail('Vigane alguskuupäev', 400, ['dateFrom' => $e->getMessage()]);
            }
        }

        if (!empty($dateUntil)) {
            try {
                $until = new DateTime($dateUntil);
                $reportQuery->filterByCreatedAt(['max' => $until]);
            } catch (\Exception $e) {
                return JSendResponse::createFail('Vigane lõppkuupäev', 400, ['dateUntil' => $e->getMessage()]);
            }
        }


        if (!empty($reportType)) {
            if (!in_array($reportType, Report::$types)) {
                return JSendResponse::createFail('Vigane raporti tüüp', 400, ['reportType' => 'Vigane raporti tüüp: '.$reportType]);
            }

            $reportQuery->filterByType($reportType);
        }

        $reports = $reportQuery
            ->filterByConventId($conventId)
            ->orderByCreatedAt(\Criteria::DESC)
            ->limit($limit)
            ->offset($offset)
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
     *      {"name"="inventorySource","type"="string","description"="Use 'warehouse' to take products from warehouse for update report"},
     *      {"name"="inventoryTarget","type"="string","description"="Save product counts to selected inventory: warehouse or storage"},
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
            return JSendResponse::createFail('Tegevuseks pead olema super admin', 403);
        }

        $type = $request->get('type', Report::TYPE_VERIFICATION);

        if (!in_array($type, Report::$types)) {
            return JSendResponse::createFail('Vigased parameetrid', 400, ['type' => 'Vigane raporti tüüp']);
        }

        if ($type === Report::TYPE_UPDATE && $this->isGranted(User::ROLE_ADMIN) === false) {
            return JSendResponse::createFail('Vigased parameetrid', 403, ['type' => 'Sellist tüüpi raportit saab tekitada admin']);
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
            return JSendResponse::createFail('Aruannet ei leitud', 404);
        }

        if ($report->isUpdate()) {
            return JSendResponse::createFail('Majanduseestseisja aruannet ei saa muuta, lisa uus aruanne', 400);
        }

        $conventId = $report->getConventId();
        $memberConventId = $this->getMember()->getKoondisedId();

        if ($conventId != $memberConventId && !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            return JSendResponse::createFail('Tegevuseks pead olema super admin', 403);
        }

        if ($report->getMemberId() != $this->getMember()->getId() && !$this->isGranted(User::ROLE_ADMIN)) {
            return JSendResponse::createFail('Tegevuseks pead olema admin või raporti looja', 403);
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
            return JSendResponse::createFail('Tegevuseks pead olema sisse logitud', 401);
        }

        $form = $this->createForm(new ReportType(), $report, [
            'method' => $request->getMethod(),
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var Report $report */
            $report = $form->getData();
            if ($report->isNew()) {
                $report->setMember($this->getUser()->getMember());
                $report->updateRowPrices();
            }
            $report->save();

            if (!$report->isUpdate() && $report->isLatest()) {
                // Save storage counts
                $report->saveProductCounts(Product::INVENTORY_TYPE_STORAGE);
            } else if ($report->isUpdate()) {
                $source = $request->get('inventorySource', null);
                $target = $request->get('inventoryTarget', Product::INVENTORY_TYPE_STORAGE);

                // Remove from source inventory (warehouse and in some cases storage)
                if (!empty($source)) {
                    $report->saveProductCounts($source, 'reduce');
                }

                if (!empty($target)) {
                    // Add to target inventory (warehouse or storage)
                    $report->saveProductCounts($target, 'add');
                }
            }

            $code = $request->getMethod() === 'POST' ? 201 : 200;
            return JSendResponse::createSuccess(['reportId' => $report->getId()], [], $code);
        } else {
            $errors = FormErrorHelper::getErrors($form);

            return JSendResponse::createFail('Aruande salvestamine ebaõnnestus', 400, $errors);
        }
    }
}
