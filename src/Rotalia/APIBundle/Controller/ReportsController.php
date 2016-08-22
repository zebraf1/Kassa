<?php

namespace Rotalia\APIBundle\Controller;


use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
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
     *          {"name"="dateFrom","type"="string"},
     *          {"name"="dateUntil","type"="string"},
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
            ->leftJoinReportRow('report_row')
            ->with('report_row')
            ->find()
        ;

        $resultReports = [];

        foreach ($reports as $report) {
            $resultReports[] = $report->getAjaxData();
        }

        return JSendResponse::createSuccess(['reports' => $resultReports]);
    }
}
