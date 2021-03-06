<?php

namespace Rotalia\APIBundle\Model;

use DateTime;
use Rotalia\APIBundle\Model\om\BaseReportQuery;

class ReportQuery extends BaseReportQuery
{
    /**
     * Fetch today's reports
     *
     * @return $this
     */
    public function filterByCreatedToday()
    {
        $this->filterByCreatedAt([
            'min' => 'today',
            'max' => 'tomorrow',
        ]);

        return $this;
    }

    /**
     * Find previous report with type verification
     *
     * @param Report $report
     * @return Report
     */
    public static function findPreviousVerificationReport(Report $report)
    {
        //Note: #5 - join with report row cannot be used here because it would select only 1 related report row
        return self::create()
            ->filterByType(Report::TYPE_VERIFICATION)
            ->filterByConventId($report->getConventId())
            ->filterByTarget($report->getTarget())
            ->filterByCreatedAt($report->getCreatedAt(), self::LESS_THAN)
            ->orderByCreatedAt(self::DESC)
            ->findOne();
    }

    /**
     * Find update reports that were created between reports 1 and 2
     * Assumes that reports are from the same convent
     *
     * @param $conventId
     * @param DateTime|null $dateFrom
     * @param DateTime|null $dateUntil
     * @return Report[]
     * @throws \PropelException
     */
    public static function findUpdateReportsBetween($conventId, DateTime $dateFrom = null, DateTime $dateUntil = null)
    {

        $query = self::create()
            ->filterByType(Report::TYPE_UPDATE)
            ->filterByConventId($conventId);

        if ($dateFrom !== null) {
            $query->filterByCreatedAt($dateFrom, self::GREATER_THAN);
        }

        if ($dateUntil !== null) {
            $query->filterByCreatedAt($dateUntil, self::LESS_EQUAL);
        }

        return $query
            ->joinReportRow('report_row')
            ->with('report_row')
            ->find();
    }
}
