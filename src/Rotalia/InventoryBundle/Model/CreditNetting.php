<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseCreditNetting;

class CreditNetting extends BaseCreditNetting
{
    /**
     * @return array
     */
    public function getAjaxData()
    {
        $creditNettingRows = [];

        foreach ($this->getCreditNettingRows() as $creditNettingRow) {
            $creditNettingRows[] = $creditNettingRow->getAjaxData();
        }

        return [
            'id' => $this->getId(),
            'createdAt' => $this->getCreatedAt('H:i d.m.Y'),
            'creditNettingRows' => $creditNettingRows,
        ];
    }
}
