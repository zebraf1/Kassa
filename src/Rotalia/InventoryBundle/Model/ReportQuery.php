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
            ->filterByCreatedAt($report->getCreatedAt(), self::LESS_THAN)
            ->orderByCreatedAt(self::DESC)
            ->findOne();
    }

    /**
     * Find previous report with type verification
     *
     * @param Report $report1
     * @param Report $report2
     * @return Report[]
     * @throws \PropelException
     */
    public static function findUpdateReportsBetween(Report $report1, Report $report2)
    {
        return self::create()
            ->filterByType(Report::TYPE_UPDATE)
            ->filterByCreatedAt($report1->getCreatedAt(), self::GREATER_THAN)
            ->filterByCreatedAt($report2->getCreatedAt(), self::LESS_THAN)
            ->joinReportRow('report_row')
            ->with('report_row')
            ->find();
    }
}
