<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Model\om\BaseProduct;
use Rotalia\UserBundle\Model\Convent;
use Rotalia\UserBundle\Model\ConventQuery;

class Product extends BaseProduct
{
    const INVENTORY_TYPE_WAREHOUSE = 'warehouse';
    const INVENTORY_TYPE_STORAGE = 'storage';

    public $conventId = null;

    /**
     * Return amount type name
     *
     * @return string
     */
    public function getAmountTypeName()
    {
        switch ($this->getAmountType()) {
            case XClassifier::AMOUNT_TYPE_PIECE:
                return 'tk';
                break;
            case XClassifier::AMOUNT_TYPE_LITRE:
                return 'L';
                break;
            case XClassifier::AMOUNT_TYPE_KG:
                return 'kg';
                break;
            case XClassifier::AMOUNT_TYPE_G:
                return 'g';
                break;
            default:
                return $this->getAmountType();
                break;
        }
    }


    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getStatus() == XClassifier::STATUS_ACTIVE;
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
    public function setStatus($v)
    {
        // todo remove
        parent::setStatus($v);

        if ($info = $this->getProductInfo()) {
            $info->setStatus($v);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrice()
    {
        if ($info = $this->getProductInfo()) {
            return $info->getPrice();
        }

        // todo remove
        return parent::getPrice();
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        if ($info = $this->getProductInfo()) {
            return $info->getStatus();
        }

        // todo remove
        return parent::getStatus();
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
     * @return null|string
     */
    public function getStatusType()
    {
        if ($info = $this->getProductInfo()) {
            return $info->getStatusType();
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAjaxData()
    {
        $productInfo = $this->getProductInfo();

        return [
            'id' => $this->getId(),
            'name' => $this->getAjaxName(),
            'productCodes' => explode(',', $this->getProductCode()),
            'price' => $productInfo ? doubleval($productInfo->getPrice()) : null,
            'amount' => doubleval($this->getAmount()),
            'amountType' => $this->getAmountType(),
            'status' => $productInfo ? $productInfo->getStatus() : null,
            'productGroupId' => $this->getProductGroupId(),
            'inventoryAmounts' => $productInfo ?
                [
                    'warehouse' => doubleval($productInfo->getWarehouseAmount()),
                    'storage' => doubleval($productInfo->getStorageAmount()),
                ] : null,
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

    /**
     * Create missing ProductInfo objects for the current product and active convents
     */
    public function ensureProductInfos()
    {
        $infos = $this->getProductInfos();
        $infoConventIds = array_map(function (ProductInfo $info) { return $info->getConventId(); }, $infos->getArrayCopy());

        /** @var Convent[] $activeConvents */
        $activeConvents = ConventQuery::create()->filterByIsActive(true)->find();

        // Add entries for other active convents too that are missing
        foreach ($activeConvents as $convent) {
            if (!in_array($convent->getId(), $infoConventIds)) {
                $productInfo = new ProductInfo();
                $productInfo
                    ->setProduct($this)
                    ->setConvent($convent)
                    ->setPrice($this->getPrice()) // use the same price
                    ->setStatus(XClassifier::STATUS_DISABLED) // but set disabled
                ;
            }
        }
    }
}
