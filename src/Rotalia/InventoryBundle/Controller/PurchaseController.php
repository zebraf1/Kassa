<?php

namespace Rotalia\InventoryBundle\Controller;

/**
 * Class PurchaseController
 * @package Rotalia\InventoryBundle\Controller
 */
class PurchaseController extends DefaultController
{
    public function posAction()
    {
        return $this->render('RotaliaInventoryBundle:Purchase:pos.html.twig');
    }
}
