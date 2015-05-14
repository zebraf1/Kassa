<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseUserQuery;

class UserQuery extends BaseUserQuery
{
    public function filterByUsernameCanonical($usernameCanonical, $comparison = null) {
        return $this->filterByUsername($usernameCanonical, $comparison);
    }
}
