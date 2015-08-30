<?php

namespace Rotalia\InventoryBundle\Controller;


use Rotalia\InventoryBundle\Form\ProductPurchaseFilterForm;
use Rotalia\InventoryBundle\Model\ProductPurchaseQuery;
use Symfony\Component\HttpFoundation\Request;

class PurchaseController extends DefaultController
{
    /**
     * View purchase log
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logAction(Request $request)
    {
        $page = $request->get('page', 1);
        $resultsPerPage = 10;

        $purchaseQuery = ProductPurchaseQuery::create()
            ->orderByCreatedAt(\Criteria::DESC)
        ;

        if ($this) {

        }

        $filterForm = $this->createForm(new ProductPurchaseFilterForm());
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted()) {
            if ($filterForm->isValid()) {
                $formData = $filterForm->getData();

                if (!empty($formData['date'])) {
                    /** @var \DateTime $date */
                    $date = $formData['date'];
                    $purchaseQuery->filterByCreatedAt($date->format('Y-m-d 00:00:00'), \Criteria::GREATER_EQUAL);
                    $purchaseQuery->filterByCreatedAt($date->format('Y-m-d 23:59:59'), \Criteria::LESS_EQUAL);
                }

                if (!empty($formData['product'])) {
                    $purchaseQuery
                        ->useProductQuery()
                        ->filterByName('%'.$formData['product'].'%', \Criteria::LIKE)
                        ->endUse()
                    ;
                }

                if (!empty($formData['member'])) {
                    $purchaseQuery
                        ->useMemberQuery()
                        ->filterByFullName('%'.$formData['member'].'%', \Criteria::LIKE)
                        ->endUse()
                    ;
                }
            }
        }

        $productPurchases = $purchaseQuery->paginate($page, $resultsPerPage);

        return $this->render('RotaliaInventoryBundle:Purchase:log.html.twig', [
            'productPurchases' => $productPurchases,
            'filterForm' => $filterForm->createView(),
        ]);
    }
}
