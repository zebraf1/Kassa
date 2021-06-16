<?php

namespace Rotalia\APIBundle\Model;

use Rotalia\APIBundle\Classes\XClassifier;
use Rotalia\APIBundle\Model\om\BaseProductQuery;

class ProductQuery extends BaseProductQuery
{
    /**
     * @return Product[]|\PropelObjectCollection
     */
    public static function getActiveProductsFirst()
    {
        return self::create()
            ->orderByStatus() //ACTIVE then DISABLED
            ->orderBySeq()
            ->find()
        ;
    }

    /**
     * @return Product[]|\PropelObjectCollection
     */
    public static function getActiveProducts()
    {
        return self::create()
            ->orderBySeq()
            ->filterByStatus(XClassifier::STATUS_ACTIVE)
            ->find()
            ;
    }

    /**
     * @inheritdoc
     */
    public function filterByProductCode($productCode = null, $comparison = null)
    {
        $criterion = new \Criterion($this, 'FIND_IN_SET(?, '.ProductPeer::PRODUCT_CODE.')', $productCode, \Criteria::RAW, $comparison);
        $this->add($criterion);

        return $this;
    }
}
