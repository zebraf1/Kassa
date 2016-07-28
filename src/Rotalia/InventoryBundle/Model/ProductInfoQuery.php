<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Model\om\BaseProductInfoQuery;

class ProductInfoQuery extends BaseProductInfoQuery
{
    /**
     * @param bool $active  null - no filtering, true - active, false - disabled
     * @return $this
     */
    public function filterByActiveStatus($active)
    {
        // Do not filter
        if ($active === null) {
            return $this;
        }

        if ($active) {
            $this->filterByStatus(XClassifier::STATUS_ACTIVE);
        } else {
            $this->filterByStatus(XClassifier::STATUS_DISABLED);
        }

        return $this;
    }
}
