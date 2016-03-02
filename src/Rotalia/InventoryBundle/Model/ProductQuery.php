<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Classes\XClassifier;
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

    /**
     * @inheritdoc
     */
    public function filterByProductCode($productCode = null, $comparison = null)
    {
        $criterion = new \Criterion($this, 'FIND_IN_SET(?, '.ProductPeer::PRODUCT_CODE.')', $productCode, \Criteria::RAW, $comparison);
        $this->add($criterion);

        return $this;
    }

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
            $this->filterByStatusId(XClassifier::STATUS_ACTIVE);
        } else {
            $this->filterByStatusId(XClassifier::STATUS_DISABLED);
        }

        return $this;
    }
}
