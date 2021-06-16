<?php

namespace Rotalia\APIBundle\Model;

use Rotalia\APIBundle\Model\om\BaseCreditNettingRow;

class CreditNettingRow extends BaseCreditNettingRow
{
    /**
     * @return array
     */
    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'conventId' => $this->getConventId(),
            'convent' => $this->getConvent()->getName(),
            'sum' => doubleval($this->getSum()),
            'nettingDone' => $this->getNettingDone()
        ];
    }

}
