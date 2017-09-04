<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseReportQuery;

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
     * @throws \PropelException
     */
    public static function findPreviousVerificationReport(Report $report)
    {
        //Note: #5 - join with report row cannot be used here because it would select only 1 related report row
        return self::create()
            ->filterByType(Report::TYPE_VERIFICATION)
            ->filterByTarget($report->getTarget())
            ->filterByCreatedAt($report->getCreatedAt(), self::LESS_THAN)
            ->orderByCreatedAt(self::DESC)
            ->findOne();
    }

    /**
     * Find next report with type verification
     *
     * @param Report $report
     * @return Report
     * @throws \PropelException
     */
    public static function findNextVerificationReport(Report $report)
    {
        //Note: #5 - join with report row cannot be used here because it would select only 1 related report row
        return self::create()
            ->filterByType(Report::TYPE_VERIFICATION)
            ->filterByConventId($report->getConventId())
            ->filterByTarget($report->getTarget())
            ->filterByCreatedAt($report->getCreatedAt(), self::GREATER_THAN)
            ->orderByCreatedAt(self::ASC)
            ->findOne();
    }

    /**
     * Find update reports that were created between reports 1 and 2
     * Assumes that reports are from the same convent
     *
     * @param Report $report1
     * @param Report $report2
     * @param $conventId
     * @return Report[]
     */
    public static function findUpdateReportsBetween($conventId, Report $report1 = null, Report $report2 = null)
    {

        $query = self::create()
            ->filterByType(Report::TYPE_UPDATE)
            ->filterByConventId($conventId);

        if ($report1 !== null) {
            $query->filterByCreatedAt($report1->getCreatedAt(), self::GREATER_THAN);
        }

        if ($report2 !== null) {
            $query->filterByCreatedAt($report2->getCreatedAt(), self::LESS_EQUAL);
        }

        return $query
            ->joinReportRow('report_row')
            ->with('report_row')
            ->find();
    }
}
