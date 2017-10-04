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
     * @return int
     */
    public function getConventId()
    {
        return $this->koondised_id;
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
     * @param bool $includeCredit
     * @return array
     */
    public function getAjaxData($includeCredit=false)
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getFullName(),
            'conventId' => $this->koondised_id,
            'creditBalance' => $includeCredit ? doubleval($this->getTotalCredit()) : null
        ];
    }
}
