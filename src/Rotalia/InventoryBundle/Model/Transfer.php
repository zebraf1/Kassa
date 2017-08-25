<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseTransfer;

class Transfer extends BaseTransfer
{

    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'member' => $this->getMemberRelatedByMemberId() ? $this->getMemberRelatedByMemberId()->getAjaxName() : null,
            'convent' => $this->getConvent() ? $this->getConvent()->getName() : null,
            'sum' => doubleval($this->getSum()),
            'createdAt' => $this->getCreatedAt()->format('H:i d.m.Y'),
            'createdBy' => $this->getMemberRelatedByCreatedBy() ? $this->getMemberRelatedByCreatedBy()->getAjaxName() : null,
            'comment' => $this->getComment()
        ];
    }
}
