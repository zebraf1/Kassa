<?php

namespace Rotalia\UserBundle\Model;

use PropelPDO;
use Rotalia\UserBundle\Model\om\BaseGuardDuty;

class GuardDuty extends BaseGuardDuty
{
    /**
     * @param PropelPDO $con
     * @param bool $doQuery
     * @return Member
     */
    public function getMember(PropelPDO $con = null, $doQuery = true)
    {
        $member = parent::getMember($con, $doQuery);

        // Avoid error when invalid reference
        if ($member === null) {
            $member = new Member();
            $member->setId($this->getMemberId());
            $member->setEesnimi('Tundmatu '.$this->getMemberId());
        }

        return $member;
    }
}
