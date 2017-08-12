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
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'createdBy' => $this->getCreatedBy(),
            'comment' => $this->getComment()
        ];
    }
}
