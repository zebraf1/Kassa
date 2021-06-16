<?php

namespace Rotalia\APIBundle\Model;

use Rotalia\APIBundle\Model\om\BaseCreditNetting;

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
