<?php

namespace Rotalia\InventoryBundle\Model;

use PropelPDO;
use Rotalia\InventoryBundle\Model\om\BaseReportRow;

class ReportRow extends BaseReportRow
{
    /**
     * @return float
     */
    public function getCurrentPrice()
    {
        return doubleval($this->current_price);
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return doubleval($this->amount);
    }

    /**
     * @inheritdoc
     */
    public function preInsert(PropelPDO $con = null)
    {
        //Use related Product price and set it as current price upon insertion
        if ($this->getCurrentPrice() === null) {
            $product = $this->getProduct($con);
            $this->setCurrentPrice($product->getPrice());
        }

        return true;
    }
}
