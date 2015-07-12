<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseProductQuery;

class ProductQuery extends BaseProductQuery
{
    /**
     * @return Product[]|\PropelObjectCollection
     */
    public static function getActiveProductsFirst()
    {
        return self::create()
            ->orderByStatusId() //ACTIVE then DISABLED
            ->orderBySeq()
            ->find()
        ;
    }
}
