<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends BaseUser implements UserInterface
{
    public function getRoles()
    {
        $roles = ['ROLE_USER'];

        //TODO: fetch roles from database
        //Jaak, Kiivet, Reimo, Tõnu, Siim
        $admins = [1886, 1968, 2081, 2114, 2099];

        if (in_array($this->getLiikmedId(), $admins)) {
            $roles[] = 'ROLE_ADMIN';
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
