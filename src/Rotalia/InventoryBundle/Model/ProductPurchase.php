<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseProductPurchase;

class ProductPurchase extends BaseProductPurchase
{
    /**
     * Return total sum for the purchase
     * @return double
     */
    public function getSum()
    {
        return doubleval($this->getAmount() * $this->getCurrentPrice());
    }
}
