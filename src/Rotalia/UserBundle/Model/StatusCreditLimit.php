<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseStatusCreditLimit;

class StatusCreditLimit extends BaseStatusCreditLimit
{
    public function getAjaxData()
    {
        return [
            'statusId' => $this->getStatusId(),
            'statusName' => $this->getStatus()->getName(),
            'creditLimit' => $this->getCreditLimit(),
        ];
    }
}
