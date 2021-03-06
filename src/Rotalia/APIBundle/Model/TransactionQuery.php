<?php

namespace Rotalia\APIBundle\Model;

use DateTime;
use PropelObjectCollection;
use Rotalia\APIBundle\Model\om\BaseTransactionQuery;

class TransactionQuery extends BaseTransactionQuery
{

    public function filterByEitherMemberId($memberId): self
    {
        $this->filterByMemberId($memberId)
            ->_or()
            ->filterByCreatedBy($memberId)
        ;

        return $this;
    }

    /**
     * Find transactions that were created between $dateFrom and $dateUntil
     *
     * @param $conventId
     * @param DateTime|null $dateFrom
     * @param DateTime|null $dateUntil
     * @return Transaction[]|PropelObjectCollection
     */
    public static function findTransactionsBetween($conventId, DateTime $dateFrom = null, DateTime $dateUntil = null)
    {
        $query = self::create()
            ->filterByType(Transaction::TYPE_CREDIT_PURCHASE)
            ->filterByConventId($conventId);

        if ($dateFrom !== null) {
            $query->filterByCreatedAt($dateFrom, self::GREATER_THAN);
        }

        if ($dateUntil!== null) {
            $query->filterByCreatedAt($dateUntil, self::LESS_THAN);
        }

        return $query->find();
    }

}
