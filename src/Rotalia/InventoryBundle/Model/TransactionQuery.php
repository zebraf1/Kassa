<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseTransactionQuery;

class TransactionQuery extends BaseTransactionQuery
{

    public function filterByEitherMemberId($memberId) {

        $this->filterByMemberId($memberId)
            ->_or()
            ->filterByCreatedBy($memberId)
        ;

        return $this;
    }

    /**
     * Find transactions that were created between reports 1 and 2
     * Assumes that reports are from the same convent
     *
     * @param Report $report1
     * @param Report $report2
     * @param $conventId
     * @return Transaction[]
     */
    public static function findTransactionsBetween($conventId, Report $report1 = null, Report $report2 = null)
    {

        $query = self::create()
            ->filterByType(Transaction::TYPE_CREDIT_PURCHASE)
            ->filterByConventId($conventId);

        if ($report1 !== null) {
            $query->filterByCreatedAt($report1->getCreatedAt(), self::GREATER_THAN);
        }

        if ($report2 !== null) {
            $query->filterByCreatedAt($report2->getCreatedAt(), self::LESS_THAN);
        }

        return $query->find();
    }

}
