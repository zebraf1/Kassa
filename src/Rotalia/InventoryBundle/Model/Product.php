<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Model\om\BaseProduct;

class Product extends BaseProduct
{
    /**
     * Return amount type name
     *
     * @return string
     */
    public function getAmountType()
    {
        switch ($this->getAmountTypeId()) {
            case XClassifier::AMOUNT_TYPE_PIECE:
                return 'tk';
                break;
            case XClassifier::AMOUNT_TYPE_LITRE:
                return 'L';
                break;
            case XClassifier::AMOUNT_TYPE_KG:
                return 'kg';
                break;
            default:
                return $this->getAmountTypeId();
                break;
        }
    }

    /**
     * Return status name
     *
     * @return string
     */
    public function getStatusType()
    {
        switch ($this->getStatusId()) {
            case XClassifier::STATUS_ACTIVE:
                return 'Aktiivne';
                break;
            case XClassifier::STATUS_DISABLED:
                return 'Suletud';
                break;
            default:
                return $this->getAmountTypeId();
                break;
        }
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getStatusId() == XClassifier::STATUS_ACTIVE;
    }

    /**
     * Returns true if the Product is related to any of the given Reports
     *
     * @param Report[]|\PropelObjectCollection $reports
     * @return bool
     */
    public function isInReports($reports)
    {
        foreach ($reports as $report) {
            if ($report->getReportRowForProduct($this) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $v
     * @return Product
     */
    public function setProductCode($v)
    {
        //Trim whitespaces
        $productCodes = explode(',', $v);
        $productCodes = array_map(function($item) {return trim($item);}, $productCodes);
        $v = implode(',', $productCodes);

        return parent::setProductCode($v);
    }

    /**
     * @return string
     */
    public function getAjaxName()
    {
        return $this->getName();
    }
}
