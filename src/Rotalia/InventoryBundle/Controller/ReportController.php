<?php

namespace Rotalia\InventoryBundle\Controller;

use Rotalia\InventoryBundle\Form\ReportType;
use Rotalia\InventoryBundle\Model\ProductQuery;
use Rotalia\InventoryBundle\Model\Report;
use Rotalia\InventoryBundle\Model\ReportQuery;
use \DateTime;
use Symfony\Component\HttpFoundation\Request;

class ReportController extends DefaultController
{
    /**
     * Display Product categories and its products
     * @param Request $request
     * @throws \PropelException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $this->requireUser();
        $conventId = $this->getMember()->getConventId();
        
        $activeReport = ReportQuery::create()
            ->filterByCreatedToday()
            ->filterByType(Report::TYPE_VERIFICATION) //Avoid update reports as active
            ->filterByConventId($conventId)
            ->findOneOrCreate()
        ;

        if ($activeReport->isNew()) {
            //Fix: Save time, not just date
            $activeReport->setCreatedAt(new DateTime());
        }

        $reportForm = $this->createForm(new ReportType(), $activeReport);

        $reportForm->handleRequest($request);

        if ($reportForm->isValid()) {
            $activeReport->setMember($this->getUser()->getMember());
            $activeReport->updateRowPrices();
            $activeReport->save();
            $this->setFlashOk($request, 'Aruanne salvestatud.');
            return $this->redirect($this->generateUrl('RotaliaReport_list'));
        } elseif ($reportForm->isSubmitted()) {
            $this->setFlashError($request, $reportForm->getErrors(true));
        }

        /** @var ReportQuery $reportQuery */
        $reportQuery = ReportQuery::create()
            ->orderByCreatedAt(\Criteria::DESC)
            ->filterByConventId($conventId)
            ->limit(4);

        //Exclude active report, which is passed separately
        if (!$activeReport->isNew()) {
            $reportQuery->filterById($activeReport->getId(), \Criteria::NOT_EQUAL);
        }

        /** @var Report[]|\PropelObjectCollection $reports */
        $reports = $reportQuery->find();

        //Sort by created at asc
        $reports->uasort(function($a, $b) {
            /** @var $a Report */
            /** @var $b Report */
            return $a->getCreatedAt() > $b->getCreatedAt();
        });

        $products = ProductQuery::getActiveProductsFirst();

        return $this->render('RotaliaInventoryBundle:Report:list.html.twig', [
            'products' => $products,
            'reports' => $reports,
            'activeReport' => $activeReport,
            'newReport' => new Report(),
            'reportForm' => $reportForm->createView(),
        ]);
    }

    /**
     * Browse old reports
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function browseAction(Request $request)
    {
        $this->requireUser();

        $conventId = $this->getMember()->getConventId();

        $page = $request->get('page', 1);

        $products = ProductQuery::getActiveProductsFirst();

        $reports = ReportQuery::create()
            ->orderByCreatedAt(\Criteria::DESC)
            ->filterByConventId($conventId)
            ->paginate($page, 5)
        ;

        //Sort by created at asc
        $reports->getIterator()->uasort(function($a, $b) {
            /** @var $a Report */
            /** @var $b Report */
            return $a->getCreatedAt() > $b->getCreatedAt();
        });

        return $this->render('RotaliaInventoryBundle:Report:browse.html.twig', [
            'products' => $products,
            'reports' => $reports,
        ]);
    }

    /**
     * View a single report
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \PropelException
     */
    public function viewAction($id)
    {
        $this->requireUser();

        /** @var Report $report */
        $report = ReportQuery::create()
            ->useReportRowQuery('report_row')
                ->useProductQuery('product')
                    ->orderBySeq()
                ->endUse()
            ->endUse()
            ->with('report_row')
            ->with('product')
            ->findPk($id)
        ;

        $products = [];

        foreach ($report->getReportRows() as $row) {
            $products[] = $row->getProduct();
        }

        return $this->render('RotaliaInventoryBundle:Report:report.html.twig', [
            'products' => $products,
            'report' => $report,
        ]);
    }

    /**
     * Update a single report
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \PropelException
     */
    public function updateAction(Request $request, $id = null)
    {
        $this->requireAdmin();

        $isEditForm = $id !== null;

        if (!$isEditForm) {
            $report = new Report();
            $report->setCreatedAt(new DateTime());
            $report->setType(Report::TYPE_UPDATE);

            $products = ProductQuery::getActiveProducts();
        } else {
            /** @var Report $report */
            $report = ReportQuery::create()
                ->useReportRowQuery('report_row')
                ->useProductQuery('product')
                ->orderBySeq()
                ->endUse()
                ->endUse()
                ->with('report_row')
                ->with('product')
                ->findPk($id)
            ;

            $products = [];

            foreach ($report->getReportRows() as $row) {
                $products[] = $row->getProduct();
            }
        }

        $reportForm = $this->createForm(new ReportType($isEditForm), $report);

        $reportForm->handleRequest($request);

        if ($reportForm->isValid()) {
            if ($report->isNew()) {
                $report->setMember($this->getUser()->getMember());
                $report->updateRowPrices();
            }
            $report->save();
            $this->setFlashOk($request, 'Aruanne salvestatud.');

            return $this->redirect($this->generateUrl('RotaliaReport_list'));
        } elseif ($reportForm->isSubmitted()) {
            $this->setFlashError($request, 'Salvestamine ebaõnnestus');
        }

        return $this->render('RotaliaInventoryBundle:Report:update.html.twig', [
            'products' => $products,
            'report' => $report,
            'reportForm' => $reportForm->createView(),
            'isEditForm' => $isEditForm,
        ]);
    }
}
