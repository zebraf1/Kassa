<?php

namespace Rotalia\APIBundle\Classes;

use DateTime;
use Rotalia\InventoryBundle\Classes\XClassifier;
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
     * @param int $conventId
     * @param $resourceType
     * @param Report $report1
     * @param Report|null $report2
     * @return array - The values are positive, if moving from the store -> warehouse -> storage -> buyer
     * @throws \PropelException
     */
    public static function getUpdatesBetweenReports($target, $conventId, $resourceType, Report $report1 = null, Report $report2 = null)
    {
        return self::getUpdatesBetweenDates($target, $conventId, $resourceType,
            $report1 == null ? null : $report1->getCreatedAt(),
            $report2 == null ? null : $report2->getCreatedAt());
    }

    /**
     * internal_in and internal_out mean transfers between storage and warehouse.
     *
     * @param $target
     * @param $conventId
     * @param $resourceType
     * @param DateTime $dateFrom
     * @param DateTime $dateUntil
     * @return mixed
     * @throws \PropelException
     */
    public static function getUpdatesBetweenDates($target, $conventId, $resourceType, DateTime $dateFrom = null, DateTime $dateUntil = null) {

        $updates = [
            'cash' => [
                'in' => 0,
                'out' => 0,
                'internal_in' => 0,
                'internal_out' => 0
            ],
            'products' => []
        ];

        if ($target == Product::INVENTORY_TYPE_STORAGE) {
            $transactions = TransactionQuery::findTransactionsBetween($conventId, $dateFrom, $dateUntil);

            /** @var Transaction $transaction */
            foreach ($transactions as $transaction) {
                if ($transaction->getProduct()->getResourceType() === $resourceType) {
                    if (!array_key_exists($transaction->getProductId(), $updates['products'])) {
                        $updates['products'][$transaction->getProductId()] = [
                            'in' => 0,
                            'internal_in' => 0,
                            'out' => 0,
                            'internal_out' => 0,
                            'total_price_out' => 0
                        ];
                    }
                    $updates['products'][$transaction->getProductId()]['out'] += intval($transaction->getCount());
                    $updates['products'][$transaction->getProductId()]['total_price_out'] += floatval($transaction->getSum());
                }
            }

        }

        if ($resourceType === XClassifier::RESOURCE_TYPE_LIMITED) {
            $updateReports = ReportQuery::findUpdateReportsBetween($conventId, $dateFrom, $dateUntil);
            return self::collectUpdates($updateReports, $updates, $target);
        } else {
            return $updates;
        }
    }

    private static function collectUpdates($updateReports, $updates, $target) {
        /** @var Report $updateReport */
        foreach ($updateReports as $updateReport) {
            if ($updateReport->getTarget() == $target) {
                $direction = 'in';
            } elseif ($updateReport->getSource() == $target) {
                $direction = 'out';
            } else {
                continue;
            }

            $isInternalUpdate = (
                    $updateReport->getTarget() === Product::INVENTORY_TYPE_WAREHOUSE && $updateReport->getSource() === Product::INVENTORY_TYPE_STORAGE
                ) || (
                    $updateReport->getTarget() === Product::INVENTORY_TYPE_STORAGE && $updateReport->getSource() === Product::INVENTORY_TYPE_WAREHOUSE
                );

            $updates['cash'][$direction] += $updateReport->getCash();
            if ($isInternalUpdate) {
                $updates['cash']['internal_'.$direction] += $updateReport->getCash();
            }

            foreach ($updateReport->getReportRows() as $reportRow) {
                if(!array_key_exists($reportRow->getProductId(), $updates['products'])) {
                    $updates['products'][$reportRow->getProductId()] = [
                        'in' => 0,
                        'internal_in' => 0,
                        'out' => 0,
                        'internal_out' => 0,
                        'total_price_out' => 0
                    ];
                }

                $updates['products'][$reportRow->getProductId()][$direction] += $reportRow->getCount();
                if ($isInternalUpdate) {
                    $updates['products'][$reportRow->getProductId()]['internal_'.$direction] += $reportRow->getCount();
                } else {
                    if ($direction === 'out') {
                        $updates['products'][$reportRow->getProductId()]['total_price_out'] += $reportRow->getCurrentPrice() * $reportRow->getCount();
                    }
                }

            }
        }
        return $updates;
    }

    /**
     * @param $target string - storage|warehouse
     * @param $conventId int
     * @param DateTime $date
     * @return array
     * @throws \PropelException
     */
    public static function findAmountsAtDate($target, $conventId, $date) {

        $previousVerification = ReportQuery::create()
            ->filterByType(Report::TYPE_VERIFICATION)
            ->filterByConventId($conventId)
            ->filterByTarget($target)
            ->filterByCreatedAt($date, ReportQuery::LESS_THAN)
            ->orderByCreatedAt(ReportQuery::DESC)
            ->findOne();

        $updates = self::getUpdatesBetweenDates($target, $conventId, XClassifier::RESOURCE_TYPE_LIMITED,
            $previousVerification === null ? null : $previousVerification->getCreatedAt(), $date);

        // Initial
        $expectedCash = 0;
        $expectedProductCounts = [];
        $prices = [];
        if ($previousVerification) {
            $expectedCash = $previousVerification->getCash();

            foreach ($previousVerification->getReportRows() as $row) {
                if ($row->getProduct()->getResourceType() === XClassifier::RESOURCE_TYPE_LIMITED) {
                    $expectedProductCounts[$row->getProductId()] = $row->getCount();
                    $prices[$row->getProductId()] = $row->getCurrentPrice();
                }
            }
        }

        // Updates
        $expectedCash += $updates['cash']['in'] - $updates['cash']['out'];
        foreach ($updates['products'] as $productId => $productUpdates) {
            if (array_key_exists($productId, $expectedProductCounts)) {
                $expectedProductCounts[$productId] += $productUpdates['in'] - $productUpdates['out'];
            } else {
                $expectedProductCounts[$productId] = $productUpdates['in'] - $productUpdates['out'];
            }
        }

        return [
            'cash' => $expectedCash,
            'products' => $expectedProductCounts
        ];
    }
}