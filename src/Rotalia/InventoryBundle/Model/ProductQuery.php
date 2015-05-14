<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Model\om\BaseProductQuery;

class ProductQuery extends BaseProductQuery
{
    /**
     * @return Product[]|\PropelObjectCollection
     */
    public static function getActiveProducts()
    {
        return self::create()
            ->orderBySeq()
            ->filterByStatusId(XClassifier::STATUS_ACTIVE)
            ->find()
        ;
    }
}
