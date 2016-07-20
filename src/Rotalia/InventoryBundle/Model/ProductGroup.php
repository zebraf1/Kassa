<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseProductGroup;

class ProductGroup extends BaseProductGroup
{
    /**
     * @return string
     */
    public function getAjaxName()
    {
        return $this->getName();
    }

    /**
     * @return array
     */
    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getAjaxName(),
            'seq' => $this->getSeq(),
        ];
    }
}
