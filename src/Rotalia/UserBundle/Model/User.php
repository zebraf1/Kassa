<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends BaseUser implements UserInterface
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_USER = 'ROLE_USER';

    public function getRoles()
    {
        $roles = [self::ROLE_USER];

        //TODO: fetch roles from database
        //Jaak, Kiivet, Reimo, Tõnu, Siim, Imre, Tanel Kiis
        $admins = [1886, 1968, 2081, 2114, 2099, 2073, 2164];

        if (in_array($this->getLiikmedId(), $admins)) {
            $roles[] = self::ROLE_ADMIN;
        }

        return $roles;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        return;
    }
}
