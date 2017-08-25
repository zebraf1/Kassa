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
    public function getStorageCount()
    {
        return doubleval($this->storage_count);
    }

    /**
     * @return float
     */
    public function getWarehouseCount()
    {
        return doubleval($this->warehouse_count);
    }

    /**
     * Reduce storage amount by the given amount
     * @param $count
     * @return ProductInfo
     */
    public function reduceStorageCount($count)
    {
        return $this->setStorageCount($this->getStorageCount() - $count);
    }

    /**
     * Add storage amount by the given amount
     * @param $count
     * @return ProductInfo
     */
    public function addStorageCount($count)
    {
        return $this->setStorageCount($this->getStorageCount() + $count);
    }

    /**
     * Reduce warehouse amount by the given amount
     * @param $count
     * @return ProductInfo
     */
    public function reduceWarehouseCount($count)
    {
        return $this->setWarehouseCount($this->getWarehouseCount() - $count);
    }

    /**
     * Add warehouse amount by the given amount
     * @param $count
     * @return ProductInfo
     */
    public function addWarehouseCount($count)
    {
        return $this->setWarehouseCount($this->getWarehouseCount() + $count);
    }
}
