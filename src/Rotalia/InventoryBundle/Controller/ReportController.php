<?php

namespace Rotalia\InventoryBundle\Controller;


use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Form\ReportType;
use Rotalia\InventoryBundle\Model\Product;
use Rotalia\InventoryBundle\Model\ProductQuery;
use Rotalia\InventoryBundle\Model\Report;
use Rotalia\InventoryBundle\Model\ReportQuery;
use \DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\Date;

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
        $isUpdate = $request->get('update', false);
        if ($isUpdate && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        if ($isUpdate) {
            $activeReport = new Report();
            $activeReport->setCreatedAt(new DateTime());
            $activeReport->setType(Report::TYPE_UPDATE);
        } else {
            $activeReport = ReportQuery::create()
                ->filterByCreatedToday()
                ->filterByType(Report::TYPE_VERIFICATION) //Avoid update reports as active
                ->findOneOrCreate()
            ;
            if ($activeReport->isNew()) {
                //Fix: Save time, not just date
                $activeReport->setCreatedAt(new DateTime());
            }
        }

        $reportForm = $this->createForm(new ReportType(), $activeReport);

        $reportForm->handleRequest($request);

        if ($reportForm->isValid()) {
            $activeReport->setMember($this->getUser()->getMember());
            $activeReport->updateRowPrices();
            $activeReport->save();
            $this->setFlash('ok', 'Aruanne salvestatud.');
            return $this->redirect($this->generateUrl('RotaliaReport_list'));
        } elseif ($reportForm->isSubmitted()) {
            $this->setFlash('error', 'Aruande sisestamisel tekkis vigu!');
        }

        if (!$isUpdate) {
            /** @var ReportQuery $reportQuery */
            $reportQuery = ReportQuery::create()
                ->orderByCreatedAt(\Criteria::DESC)
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
                'reportForm' => $reportForm->createView(),
            ]);
        } else {
            $products = ProductQuery::getActiveProducts();

            return $this->render('RotaliaInventoryBundle:Report:update.html.twig', [
                'products' => $products,
                'activeReport' => $activeReport,
                'reportForm' => $reportForm->createView(),
            ]);
        }
    }

    /**
     * Browse old reports
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function browseAction(Request $request)
    {
        $page = $request->get('page', 1);

        $products = ProductQuery::getActiveProductsFirst();

        $reports = ReportQuery::create()
            ->orderByCreatedAt(\Criteria::DESC)
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
}
