<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseTransfer;

class Transfer extends BaseTransfer
{

    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'member' => $this->getMemberRelatedByMemberId()->getAjaxName(),
            'convent' => $this->getConvent()->getName(),
            'sum' => doubleval($this->getSum()),
            'createdAt' => $this->getCreatedAt()->format('H:i d.m.Y'),
            'createdBy' => $this->getMemberRelatedByCreatedBy() ? $this->getMemberRelatedByCreatedBy()->getAjaxName() : null,
            'comment' => $this->getComment()
        ];
    }
}
