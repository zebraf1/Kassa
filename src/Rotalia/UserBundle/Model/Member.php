<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseMember;

class Member extends BaseMember
{
    public function getFullName()
    {
        return $this->getEesnimi() . ' ' . $this->getPerenimi();
    }
}
