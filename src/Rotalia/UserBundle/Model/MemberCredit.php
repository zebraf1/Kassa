<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\APIBundle\Classes\OutOfCreditException;
use Rotalia\UserBundle\Model\om\BaseMemberCredit;

class MemberCredit extends BaseMemberCredit
{
    const STATUS_NEGATIVE = 'negative';
    const STATUS_NULL = 'null';
    const STATUS_POSITIVE = 'positive';

    public function getCreditStatus()
    {
        if ($this->getCredit() > 0) {
            return self::STATUS_POSITIVE;
        } else if ($this->getCredit() < 0) {
            return self::STATUS_NEGATIVE;
        } else {
            return self::STATUS_NULL;
        }
    }

    /**
     * @param      $amount
     *
     * @param null $creditLimit
     *
     * @return $this
     * @throws OutOfCreditException
     */
    public function adjustCredit($amount, $creditLimit = null)
    {
        $currentCredit = doubleval($this->getCredit());
        $amount = doubleval($amount);
        $newCredit = round($currentCredit + $amount, 2);

        if ($creditLimit !== null) {
            // The MemberCredit is per convent. Compare total credit against the credit limit
            $totalCredit = doubleval($this->getMember()->getTotalCredit());
            $newTotalCredit = round($totalCredit + $amount, 2);

            if ($newTotalCredit < $creditLimit) {
                throw new OutOfCreditException($newTotalCredit, $creditLimit);
            }
        }

        $this->setCredit($newCredit);

        return $this;
    }
}
