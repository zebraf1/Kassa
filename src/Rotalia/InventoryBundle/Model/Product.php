<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Model\om\BaseProduct;

class Product extends BaseProduct
{
    public $conventId = null;

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
     * @inheritdoc
     */
    public function setPrice($v)
    {
        // todo remove
        parent::setPrice($v);

        if ($info = $this->getProductInfo()) {
            $info->setPrice($v);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setStatusId($v)
    {
        // todo remove
        parent::setStatusId($v);

        if ($info = $this->getProductInfo()) {
            $info->setStatusId($v);
        }

        return $this;
    }

    /**
     * @param $v
     * @return $this
     */
    public function setConventId($v)
    {
        $this->conventId = $v;

        return $this;
    }

    /**
     * @return string
     */
    public function getAjaxName()
    {
        return $this->getName();
    }

    /**
     * Get product info for convent id
     * @return mixed|null|ProductInfo
     */
    public function getProductInfo()
    {
        $conventId = $this->conventId;

        if ($conventId == null) {
            return null;
        }

        foreach ($this->getProductInfos() as $info) {
            if ($info->getConventId() == $conventId) {
                return $info;
            }
        }

        $productInfo = new ProductInfo();
        $productInfo->setConventId($conventId);
        $productInfo->setProduct($this);

        return $productInfo;
    }

    /**
     * @return string
     */
    public function getAjaxData()
    {
        $productInfo = $this->getProductInfo();

        return [
            'id' => $this->getId(),
            'name' => $this->getAjaxName(),
            'productCode' => $this->getProductCode(),
            'price' => $productInfo ? doubleval($productInfo->getPrice()) : null,
            'unit' => $this->getAmountTypeId(),
            'unitName' => $this->getAmountType(),
            'status' => $productInfo ? $productInfo->getStatusId() : null,
            'statusName' => $productInfo ? $productInfo->getStatusType() : null,
            'productGroupId' => $this->getProductGroupId(),
        ];
    }

    /**
     * FK Validation
     * @return bool
     */
    public function isProductGroupValid()
    {
        return $this->getProductGroupId() === null || $this->getProductGroup() !== null;
    }
}
