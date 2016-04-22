<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseMemberQuery;

class MemberQuery extends BaseMemberQuery
{
    /**
     * @param $names
     * @param string $criteria
     * @return $this
     */
    public function filterByFullName($names, $criteria = \Criteria::EQUAL)
    {
        $this
            ->where('CONCAT_WS(" ", '.$this->getModelAliasOrName().'.eesnimi, '.$this->getModelAliasOrName().'.perenimi) '.$criteria.' ?', $names)
            ->_or()
            ->where('CONCAT_WS(" ", '.$this->getModelAliasOrName().'.perenimi, '.$this->getModelAliasOrName().'.eesnimi) '.$criteria.' ?', $names)
        ;

        return $this;
    }

    /**
     * @param string $order
     * @return $this
     */
    public function orderByMemberCredit($order = \Criteria::ASC)
    {
        if ($order === \Criteria::ASC) {
            $this->addAscendingOrderByColumn('IF(member_credit.id IS NULL, 0, member_credit.credit)');
        } elseif ($order === \Criteria::DESC) {
            $this->addDescendingOrderByColumn('IF(member_credit.id IS NULL, 0, member_credit.credit)');
        }

        return $this;
    }
}
