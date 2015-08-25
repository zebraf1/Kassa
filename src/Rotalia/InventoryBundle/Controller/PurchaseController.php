<?php

namespace Rotalia\InventoryBundle\Controller;


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

        $productPurchases = ProductPurchaseQuery::create()
            ->orderByCreatedAt(\Criteria::DESC)
            ->paginate($page, $resultsPerPage)
        ;

        return $this->render('RotaliaInventoryBundle:Purchase:log.html.twig', [
            'productPurchases' => $productPurchases,
        ]);
    }
}
