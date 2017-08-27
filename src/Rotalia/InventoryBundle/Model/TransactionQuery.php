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
}
