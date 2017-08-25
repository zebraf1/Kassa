<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BasePointOfSale;

class PointOfSale extends BasePointOfSale
{
    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'deviceInfo' => $this->getDeviceInfo(),
            'convent' => $this->getConvent() ? $this->getConvent()->getName() : null,
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'createdBy' => $this->getMember() ? $this->getMember()->getAjaxName() : null,
        ];
    }
}
