<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseMember;

class Member extends BaseMember
{
    private $credit;

    public function getFullName()
    {
        return $this->getEesnimi() . ' ' . $this->getPerenimi();
    }

    /**
     * @return MemberCredit
     * @throws \PropelException
     */
    public function getCredit()
    {
        //Local cache
        if ($this->credit !== null) {
            return $this->credit;
        }

        $credit = MemberCreditQuery::create()
            ->filterByMember($this)
            ->findOneOrCreate();

        $this->credit = $credit;

        return $credit;
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
     * @return string
     */
    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getFullName(),
            'convent_id' => $this->koondised_id,
            'credit' => doubleval($this->getCredit()->getCredit())
        ];
    }
}
