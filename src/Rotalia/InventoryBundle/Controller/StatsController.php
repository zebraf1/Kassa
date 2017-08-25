<?php

namespace Rotalia\InventoryBundle\Controller;


use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Model\Report;
use Rotalia\InventoryBundle\Model\ReportQuery;
use \DateTime;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class StatsController extends DefaultController
{
    /**
     * Display Product statistics
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function productsAction()
    {
        $this->requireAdmin();

        //Find all reports within last 30 days
        $periodStart = new DateTime('-30 days');
        $periodEnd = new DateTime('now');

        /** @var Report[] $reports */
        $reports = ReportQuery::create()
            ->filterByCreatedAt($periodStart->format('c'), \Criteria::GREATER_EQUAL)
            ->filterByCreatedAt($periodEnd->format('c'), \Criteria::LESS_EQUAL)
            ->orderByCreatedAt()
            ->joinReportRow('report_row', \Criteria::INNER_JOIN)
            ->useReportRowQuery('product', \Criteria::INNER_JOIN)
                ->useProductQuery(null, \Criteria::INNER_JOIN)
                    ->filterByStatus(XClassifier::STATUS_ACTIVE)
                ->endUse()
            ->endUse()
            ->with('report_row')
            ->with('product')
            ->find()
        ;

        $tempTable = [];
        /** @var DateTime $firstDay */
        $firstDay = null;
        /** @var DateTime $lastDay */
        $lastDay = null;

        foreach ($reports as $report) {
            foreach ($report->getReportRows() as $reportRow) {
                $productId = $reportRow->getProductId();

                if ($report->isUpdate() && !isset($tempTable[$productId])) {
                    continue; //Skip all updates before the initial value is set for a product
                }

                if ($firstDay === null) {
                    $firstDay = $report->getCreatedAt();
                }

                if (!isset($tempTable[$productId])) {
                    $tempTable[$productId] = [
                        'initAmount' => $reportRow->getCount(),
                        'addedAmount' => 0,
                        'finalAmount' => 0,
                        'productName' => $reportRow->getProduct()->getName()
                    ];
                }

                if ($report->isUpdate()) {
                    $tempTable[$productId]['addedAmount'] += $reportRow->getCount();
                    //When last report is update, set final amount as last report + added amount
                    $tempTable[$productId]['finalAmount'] += $reportRow->getCount();
                } else {
                    $tempTable[$productId]['finalAmount'] = $reportRow->getCount();
                }
            }

            $lastDay = $report->getCreatedAt();
        }

        $daysBetween = $firstDay !== null ? $lastDay->diff($firstDay)->d: 30;

        $resultTable = [];

        setlocale(LC_TIME, 'et_EE');

        foreach ($tempTable as $productId => $amounts) {
            $consumed = $amounts['initAmount'] + $amounts['addedAmount'] - $amounts['finalAmount'];
            $avgConsumption = $consumed > 0 ? round($consumed / $daysBetween, 1) : 0;
            if ($amounts['finalAmount'] > 0 && $avgConsumption > 0) {
                $supplyForDays = round($amounts['finalAmount'] / $avgConsumption);
                $availableUntil = utf8_encode(strftime('%e. %B %Y', strtotime('+'.$supplyForDays.' day')));
                $sort = 2;
            } else {
                $supplyForDays = null;
                if ($amounts['finalAmount'] <= 0 && $avgConsumption > 0) {
                    $availableUntil = 'Toode on otsas, telli juurde';
                    $sort = 1;
                } elseif ($amounts['finalAmount'] <= 0 && $avgConsumption <= 0) {
                    $availableUntil = 'Toode on otsas ja seda ei kulu';
                    $sort = 4;
                } elseif ($amounts['finalAmount'] > 0 && $avgConsumption <= 0) {
                    $availableUntil = 'Toodet ei kulu või on täiendatud';
                    $sort = 3;
                } else {
                    $availableUntil = '?';
                    $sort = 5;
                }
            }

            $monthlySupply = round($avgConsumption * $daysBetween);

            $resultTable[] = [
                'productName' => $amounts['productName'],
                'avgConsumption' => $avgConsumption,
                'availableUntil' => $availableUntil,
                'monthlySupply' => $monthlySupply,
                'currentSupply' => $amounts['finalAmount'],
                'supplyForDays' => $supplyForDays,
                'sort' => $sort,
            ];
        }

        //Sort results
        for ($i = 0; $i < count($resultTable); $i++) {
            for ($j = $i + 1; $j < count($resultTable); $j++) {
                if ($resultTable[$i]['sort'] < $resultTable[$j]['sort']){
                    continue; //Already in order
                }

                //When sort is equal and 2, then compare available until
                if ($resultTable[$i]['sort'] > $resultTable[$j]['sort']
                    || $resultTable[$i]['sort'] == 2 && $resultTable[$i]['supplyForDays'] > $resultTable[$j]['supplyForDays']
                    || $resultTable[$i]['sort'] == 3 && $resultTable[$i]['currentSupply'] > $resultTable[$j]['currentSupply']
                ) {
                    $tempSort = $resultTable[$i];
                    $resultTable[$i] = $resultTable[$j];
                    $resultTable[$j] = $tempSort;
                }
            }
        }

        return $this->render('RotaliaInventoryBundle:Stats:products.html.twig', [
            'resultTable' => $resultTable
        ]);
    }
}
