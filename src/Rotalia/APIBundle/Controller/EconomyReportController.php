<?php

namespace Rotalia\APIBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc; // Used for API documentation
use Criteria;
use DateTime;
use Rotalia\APIBundle\Classes\Updates;
use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Model\Product;
use Rotalia\InventoryBundle\Model\Report;
use Rotalia\InventoryBundle\Model\ReportQuery;
use Rotalia\InventoryBundle\Model\ReportRow;
use Rotalia\InventoryBundle\Model\ReportRowQuery;
use Rotalia\UserBundle\Model\User;
use Symfony\Component\HttpFoundation\Request;

class EconomyReportController extends DefaultController
{
    /**
     *
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          400 = "Returned when request params are invalid",
     *          403 = "Returned when user is not authenticated",
     *     },
     *     description="Get economy report for given time period",
     *     section="EconomyReports",
     *     filters={
     *          {"name"="conventId","type"="int","description"="Fetch economy report for convent other than members home"},
     *          {"name"="dateFrom","type"="string","description":"Datetime string"},
     *          {"name"="dateUntil","type"="string","description":"Datetime string"},
     *     }
     * )
     *
     * @param Request $request
     * @return JSendResponse
     */
    public function getAction(Request $request)
    {
        $conventId = $request->get('conventId', null);
        $dateFrom = $request->get('dateFrom', null);
        $dateUntil = $request->get('dateUntil', null);

        $memberConventId = $this->getMember()->getKoondisedId();

        if ($conventId === null) {
            $conventId = $memberConventId;
        }

        if (!$this->isGranted(User::ROLE_ADMIN)) {
            return JSendResponse::createFail('Ainult admin saab näha majandus aruandeid', 403);
        }

        if ($conventId != $memberConventId && !$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            return JSendResponse::createFail('Teise konvendi majandus aruandeid saab näha ainult super admin', 403);
        }

        $dateFrom = new DateTime($dateFrom === null ? '2000-01-01' : $dateFrom); // ancient history
        $dateUntil = new DateTime($dateUntil === null ? 'now' : $dateUntil);
        //Makes sure that the end date is included in the filter.
        $dateUntil->modify('+1 day');


        // LIMITED products
        $initialAmounts = [
            Product::INVENTORY_TYPE_STORAGE => Updates::findAmountsAtDate(Product::INVENTORY_TYPE_STORAGE, $conventId, $dateFrom),
            Product::INVENTORY_TYPE_WAREHOUSE => Updates::findAmountsAtDate(Product::INVENTORY_TYPE_WAREHOUSE, $conventId, $dateFrom),
        ];

        $finalAmounts = [
            Product::INVENTORY_TYPE_STORAGE => Updates::findAmountsAtDate(Product::INVENTORY_TYPE_STORAGE, $conventId, $dateUntil),
            Product::INVENTORY_TYPE_WAREHOUSE => Updates::findAmountsAtDate(Product::INVENTORY_TYPE_WAREHOUSE, $conventId, $dateUntil),
        ];

        $updates = [
            Product::INVENTORY_TYPE_STORAGE => Updates::getUpdatesBetweenDates(Product::INVENTORY_TYPE_STORAGE, $conventId, XClassifier::RESOURCE_TYPE_LIMITED, $dateFrom, $dateUntil),
            Product::INVENTORY_TYPE_WAREHOUSE => Updates::getUpdatesBetweenDates(Product::INVENTORY_TYPE_WAREHOUSE, $conventId, XClassifier::RESOURCE_TYPE_LIMITED, $dateFrom, $dateUntil)
        ];

        $limitedResults = [];
        $cash = [];

        // Merge
        foreach (Product::$types as $target) {
            $cash[$target]['initial'] = round($initialAmounts[$target]['cash'], 2);
            $cash[$target]['in'] = round($updates[$target]['cash']['in'], 2);
            $cash[$target]['internal_in'] = round($updates[$target]['cash']['internal_in'], 2);
            $cash[$target]['out'] = round($updates[$target]['cash']['out'], 2);
            $cash[$target]['internal_out'] = round($updates[$target]['cash']['internal_out'], 2);
            $cash[$target]['final'] = round($finalAmounts[$target]['cash'], 2);

            foreach ($initialAmounts[$target]['products'] as $id => $count) {
                $limitedResults[$id][$target] = [
                    'initial' => $count,
                    'in' => 0,
                    'internal_in' => 0,
                    'out' => 0,
                    'internal_out' => 0,
                    'average_price_out' => 0,
                    'final' => 0
                ];
            }

            foreach ($updates[$target]['products'] as $id => $counts) {
                if (array_key_exists($id, $limitedResults) && array_key_exists($target, $limitedResults[$id])) {
                    $limitedResults[$id][$target]['in'] = $counts['in'];
                    $limitedResults[$id][$target]['internal_in'] = $counts['internal_in'];
                    $limitedResults[$id][$target]['out'] = $counts['out'];
                    $limitedResults[$id][$target]['internal_out'] = $counts['internal_out'];
                    $limitedResults[$id][$target]['average_price_out'] = ($counts['out'] - $counts['internal_out']) ==
                    0 ? 0 : round($counts['total_price_out'] / ($counts['out'] - $counts['internal_out']), 2);
                } else {
                    $limitedResults[$id][$target] = [
                        'initial' => 0,
                        'in' => $counts['in'],
                        'internal_in' => $counts['internal_in'],
                        'out' => $counts['out'],
                        'internal_out' => $counts['internal_out'],
                        'average_price_out' => ($counts['out'] - $counts['internal_out']) ==
                        0 ? 0 : round($counts['total_price_out'] / ($counts['out'] - $counts['internal_out']), 2),
                        'final' => 0
                    ];
                }
            }

            foreach ($finalAmounts[$target]['products'] as $id => $count) {
                if (array_key_exists($id, $limitedResults) && array_key_exists($target, $limitedResults[$id])) {
                    $limitedResults[$id][$target]['final'] = $count;
                } else {
                    $limitedResults[$id][$target] = [
                        'initial' => 0,
                        'in' => 0,
                        'internal_in' => 0,
                        'out' => 0,
                        'internal_out' => 0,
                        'average_price_out' => 0,
                        'final' => $count
                    ];
                }
            }
        }

        // Incoming prices for these items
        foreach ($limitedResults as $id => $product) {
            $storageCount = !array_key_exists(Product::INVENTORY_TYPE_STORAGE, $product) ? 0 :
                $product[Product::INVENTORY_TYPE_STORAGE]['initial'] - $product[Product::INVENTORY_TYPE_STORAGE]['final'] - $product[Product::INVENTORY_TYPE_STORAGE]['internal_out'];
            $warehouseCount = !array_key_exists(Product::INVENTORY_TYPE_WAREHOUSE, $product) ? 0 :
                $product[Product::INVENTORY_TYPE_WAREHOUSE]['initial'] - $product[Product::INVENTORY_TYPE_WAREHOUSE]['final'] - $product[Product::INVENTORY_TYPE_WAREHOUSE]['internal_out'];
            $count = $storageCount + $warehouseCount;

            if ($count == 0) { //slight speedup for short queries
                $limitedResults[$id]['average_price_in'] = 0;
                continue;
            }

            // This can get slow
            $reportRowQuery = ReportRowQuery::create()
                ->filterByProductId($id)
                ->useReportQuery()
                ->filterByType(Report::TYPE_UPDATE)
                ->filterByConventId($conventId)
                ->filterBySource(null, CRITERIA::ISNULL);

            if ($dateUntil !== null) {
                $reportRowQuery->filterByCreatedAt($dateUntil, ReportQuery::LESS_EQUAL);
            }

            /** @var ReportRow[] $reportRo ws */
            $reportRows = $reportRowQuery
                ->orderBy('created_at', CRITERIA::DESC)
                ->endUse()
                ->find();

            $runningCount = 0;
            $runningPrice = 0;
            foreach ($reportRows as $reportRow) {
                $runningCount += $reportRow->getCount();
                $runningPrice += $reportRow->getCurrentPrice() * $reportRow->getCount();

                if ($runningCount >= $count) {
                    break;
                }
            }

            $limitedResults[$id]['average_price_in'] = $runningCount == 0 ? 0 : round($runningPrice / $runningCount, 2);
        }

        // Unlimited
        $updates = Updates::getUpdatesBetweenDates(Product::INVENTORY_TYPE_STORAGE, $conventId, XClassifier::RESOURCE_TYPE_UNLIMITED, $dateFrom, $dateUntil);

        $unlimitedResults = [];
        foreach ($updates['products'] as $id => $counts) {
            $unlimitedResults[$id] = [
                'out' => $counts['out'],
                'average_price_out' => $counts['out'] == 0 ? 0 : round($counts['total_price_out'] / $counts['out'], 2)
            ];
        }

        return JSendResponse::createSuccess([
            XClassifier::RESOURCE_TYPE_LIMITED => $limitedResults,
            XClassifier::RESOURCE_TYPE_UNLIMITED => $unlimitedResults,
            'cash' => $cash
        ]);
    }

}
