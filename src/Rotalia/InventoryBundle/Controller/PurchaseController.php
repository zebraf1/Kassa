<?php

namespace Rotalia\InventoryBundle\Controller;
use Rotalia\InventoryBundle\Form\ProductFilterType;
use Rotalia\InventoryBundle\Model\PointOfSaleQuery;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PurchaseController
 * @package Rotalia\InventoryBundle\Controller
 */
class PurchaseController extends DefaultController
{
    public function homeAction(Request $request)
    {
        return $this->render('RotaliaInventoryBundle:Purchase:home.html.twig', [
            'pos' => $this->getPos($request),
            'form' => $this->createForm(new ProductFilterType())->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return null|\Rotalia\InventoryBundle\Model\PointOfSale
     */
    private function getPos(Request $request)
    {
        $hash = $request->cookies->get('pos_hash');
        $pos = null;
        if ($hash) {
            $pos = PointOfSaleQuery::create()->findOneByHash($hash);
        }

        return $pos;
    }
}
