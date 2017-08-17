<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseMember;

class Member extends BaseMember
{
    public function getFullName()
    {
        return $this->getEesnimi() . ' ' . $this->getPerenimi();
    }

    /**
     * @param $conventId
     * @return MemberCredit
     */
    public function getCredit($conventId)
    {
        $credit = MemberCreditQuery::create()
            ->filterByMember($this)
            ->filterByConventId($conventId)
            ->findOneOrCreate();

        return $credit;
    }

    /**
     * @return int
     */
    public function getTotalCredit()
    {
        $credits = MemberCreditQuery::create()
            ->filterByMember($this)
            ->find();

        $totalCredit = 0;

        foreach ($credits as $credit) {
            $totalCredit += $credit->getCredit();
        }

        return $totalCredit;
    }

    /**
     * Auto-complete representation of this object
     *
     * @return string
     */
    public function getAjaxName()
    {
        return $this->getFullName();
    }

    /**
     * REST API output data for Member object
     *
     * @return array
     */
    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getFullName(),
            'conventId' => $this->koondised_id,
            'creditBalance' => doubleval($this->getTotalCredit())
        ];
    }
}
