<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Model\om\BaseProductInfo;

class ProductInfo extends BaseProductInfo
{
    /**
     * Return status name
     *
     * @return string
     */
    public function getStatusType()
    {
        switch ($this->getStatus()) {
            case XClassifier::STATUS_ACTIVE:
                return 'Aktiivne';
                break;
            case XClassifier::STATUS_DISABLED:
                return 'Suletud';
                break;
            default:
                return $this->getStatus();
                break;
        }
    }

    /**
     * @return float
     */
    public function getStorageAmount()
    {
        return doubleval($this->storage_amount);
    }

    /**
     * @return float
     */
    public function getWarehouseAmount()
    {
        return doubleval($this->warehouse_amount);
    }

    /**
     * Reduce storage amount by the given amount
     * @param $amount
     * @return ProductInfo
     */
    public function reduceStorageAmount($amount)
    {
        return $this->setStorageAmount($this->getStorageAmount() - $amount);
    }

    /**
     * Add storage amount by the given amount
     * @param $amount
     * @return ProductInfo
     */
    public function addStorageAmount($amount)
    {
        return $this->setStorageAmount($this->getStorageAmount() + $amount);
    }

    /**
     * Reduce warehouse amount by the given amount
     * @param $amount
     * @return ProductInfo
     */
    public function reduceWarehouseAmount($amount)
    {
        return $this->setWarehouseAmount($this->getWarehouseAmount() - $amount);
    }

    /**
     * Add warehouse amount by the given amount
     * @param $amount
     * @return ProductInfo
     */
    public function addWarehouseAmount($amount)
    {
        return $this->setWarehouseAmount($this->getWarehouseAmount() + $amount);
    }
}
