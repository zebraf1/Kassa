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
    public function setAmount($v)
    {
        //Convert empty string to 0
        if (empty($v)) {
            $v = 0;
        }

        return parent::setAmount($v);
    }

    /**
     * Return ReportRow amount and old price for template
     *
     * @param Product $product
     * @param string $template
     * @return float|mixed|string
     */
    public function getAmountFormatted(Product $product, $template = '{amount} <span class="oldPrice">({price}€)</span>') {
        $amount = $this->getAmount();

        if ($amount == 0) {
            return '';
        }

        if ($this->getReport()->isUpdate() && $amount > 0) {
            return '+'.$amount;
        }

        if ($this->getCurrentPrice() != $product->getPrice()) {
            $result = str_replace('{amount}', $amount, $template);
            $result = str_replace('{price}', number_format($this->getCurrentPrice(), 2), $result);
            return $result;
        }

        return $amount;
    }

    /**
     * @inheritdoc
     */
    public function preInsert(PropelPDO $con = null)
    {
        //Use related Product price and set it as current price upon insertion
        if ($this->current_price === null) {
            $this->updateCurrentPrice($con);
        }

        return true;
    }

    /**
     * Update ReportRow current price value
     *
     * @param PropelPDO $con
     */
    public function updateCurrentPrice(PropelPDO $con = null)
    {
        $product = $this->getProduct($con);
        $this->setCurrentPrice($product->getPrice());
    }
}
