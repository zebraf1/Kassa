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
        $productPurchases = ProductPurchaseQuery::create()
            ->orderByCreatedAt(\Criteria::DESC)
            ->limit(10)
            ->find()
        ;

        return $this->render('RotaliaInventoryBundle:Purchase:log.html.twig', [
            'productPurchases' => $productPurchases
        ]);
    }
}
