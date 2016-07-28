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
}
