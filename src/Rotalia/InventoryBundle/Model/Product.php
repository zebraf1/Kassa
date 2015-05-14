<?php

namespace Rotalia\InventoryBundle\Model;

use PropelPDO;
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
}
