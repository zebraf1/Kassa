<?php

namespace Rotalia\InventoryBundle\Model;

use PropelPDO;
use Rotalia\InventoryBundle\Model\om\BaseReportRow;

class ReportRow extends BaseReportRow
{
    public function getProduct(PropelPDO $con = null, $doQuery = true) {
        // Necessary for updateCurrentPrice to work properly
        $product = parent::getProduct($con, $doQuery);
        $product->setConventId($this->getReport()->getConventId());
        return $product;
    }
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
    public function getCount()
    {
        return doubleval($this->count);
    }

    /**
     * @inheritdoc
     */
    public function setCount($v)
    {
        //Convert empty string to 0
        if (empty($v)) {
            $v = 0;
        }

        return parent::setCount($v);
    }

    /**
     * Return ReportRow count and old price for template
     *
     * @param Product $product
     * @param string $template
     * @return float|mixed|string
     */
    public function getCountFormatted(Product $product, $template = '{count} <span class="oldPrice">({price}€)</span>') {
        $count = $this->getCount();

        if ($count == 0) {
            return '';
        }

        if ($this->getReport()->isUpdate() && $count > 0) {
            return '+'.$count;
        }

        if ($this->getCurrentPrice() != $product->getPrice()) {
            $result = str_replace('{count}', $count, $template);
            $result = str_replace('{price}', number_format($this->getCurrentPrice(), 2), $result);
            return $result;
        }

        return $count;
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

    public function getAjaxData()
    {
        return [
            'product' => $this->getProduct()->getAjaxData(),
            'count' => $this->getCount(),
            'currentPrice' => $this->getCurrentPrice(),
        ];
    }
}
