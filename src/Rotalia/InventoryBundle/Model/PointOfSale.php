<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BasePointOfSale;

class PointOfSale extends BasePointOfSale
{
    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getDeviceInfo(),
            'conventId' => $this->getConventId(),
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'createdBy' => $this->getMember() ? $this->getMember()->getAjaxName() : null,
        ];
    }
}
