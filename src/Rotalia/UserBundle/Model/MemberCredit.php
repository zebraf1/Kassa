<?php

namespace Rotalia\UserBundle\Model;

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
     * @param $amount
     * @return $this
     */
    public function adjustCredit($amount)
    {
        $currentCredit = doubleval($this->getCredit());
        $amount = doubleval($amount);
        $newCredit = round($currentCredit + $amount, 2);
        $this->setCredit($newCredit);

        return $this;
    }
}
