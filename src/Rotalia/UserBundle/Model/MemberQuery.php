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
}
