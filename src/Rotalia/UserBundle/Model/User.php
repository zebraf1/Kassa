<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends BaseUser implements UserInterface
{
    public function getRoles()
    {
        $roles = ['ROLE_USER'];

        //Jaak, Kiivet, Gerd, Reimo, Imre
        $admins = [1886, 1968, 2122, 2081, 2073];

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
