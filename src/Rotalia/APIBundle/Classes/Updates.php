<?php

namespace Rotalia\APIBundle\Classes;

use Rotalia\InventoryBundle\Model\Product;
use Rotalia\InventoryBundle\Model\Report;
use Rotalia\InventoryBundle\Model\ReportQuery;
use Rotalia\InventoryBundle\Model\Transaction;
use Rotalia\InventoryBundle\Model\TransactionQuery;


/**
 * Class Updates
 * Updates to cash and product counts between reports.
 *
 * @package Rotalia\APIPundle\Classes
 */
class Updates
{
    /**
     * @param $target string - storage|warehouse
     * @param $conventId
     * @param Report $report1
     * @param Report|null $report2
     * @return array - The values are positive, if moving from the store -> warehouse -> storage -> buyer
     */
    public static function getUpdates($target, $conventId, Report $report1 = null, Report $report2 = null)
    {

        $updateReports = ReportQuery::findUpdateReportsBetween($conventId, $report1, $report2);

        $updates = [
            'cash' => ['in' => 0, 'out' => 0],
            'products' => []
        ];

        switch ($target) {
            case Product::INVENTORY_TYPE_STORAGE:
                $transactions = TransactionQuery::findTransactionsBetween($conventId, $report1, $report2);

                /** @var Transaction $transaction */
                foreach ($transactions as $transaction) {
                    if(!array_key_exists($transaction->getProductId(), $updates['products'])) {
                        $updates['products'][$transaction->getProductId()] = ['in' => 0, 'out' => 0];
                    }

                    $updates['products'][$transaction->getProductId()]['out'] += intval($transaction->getCount());
                }

                /** @var Report $updateReport */
                foreach ($updateReports as $updateReport) {
                    if ($updateReport->getTarget() == Product::INVENTORY_TYPE_STORAGE) {
                        $updates['cash']['out'] += $updateReport->getCash();

                        foreach ($updateReport->getReportRows() as $reportRow) {
                            if(!array_key_exists($reportRow->getProductId(), $updates['products'])) {
                                $updates['products'][$reportRow->getProductId()] = ['in' => 0, 'out' => 0];
                            }

                            $updates['products'][$reportRow->getProductId()]['in'] += $reportRow->getCount();

                        }
                    }
                }
                break;
            case Product::INVENTORY_TYPE_WAREHOUSE:
                /** @var Report $updateReport */
                foreach ($updateReports as $updateReport) {
                    if ($updateReport->getTarget() == Product::INVENTORY_TYPE_STORAGE) {
                        $direction = 'out';
                    } else {
                        $direction = 'in';
                    }

                    $updates['cash'][$direction] += $updateReport->getCash();

                    foreach ($updateReport->getReportRows() as $reportRow) {
                        if(!array_key_exists($reportRow->getProductId(), $updates['products'])) {
                            $updates['products'][$reportRow->getProductId()] = ['in' => 0, 'out' => 0];
                        }

                        $updates['products'][$reportRow->getProductId()][$direction] += $reportRow->getCount();

                    }
                }
        }

        return $updates;
    }
}