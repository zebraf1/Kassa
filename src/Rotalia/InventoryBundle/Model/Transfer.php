<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseTransfer;

class Transfer extends BaseTransfer
{

    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'memberId' => $this->getMemberId(),
            'conventId' => $this->getConventId(),
            'sum' => doubleval($this->getSum()),
            'createdAt' => $this->getCreatedAt()->format('H:i d.m.Y'),
            'createdBy' => $this->getCreatedBy(),
            'comment' => $this->getComment()
        ];
    }
}
