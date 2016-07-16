<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseConvent;

class Convent extends BaseConvent
{
    /**
     * @return array
     */
    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
        ];
    }
}
