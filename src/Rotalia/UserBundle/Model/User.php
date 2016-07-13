<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends BaseUser implements UserInterface
{
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_USER = 'ROLE_USER';

    public function getRoles()
    {
        $roles = [self::ROLE_USER];

        //TODO: fetch roles from database
        //Reimo, Tõnu, Siim, Imre, Tanel
        $admins = [2081, 2114, 2099, 2073];

        //Jaak, Kiivet, Kiis
        $superAdmins = [1886, 1968, 2164];

        if (in_array($this->getLiikmedId(), array_merge($admins, $superAdmins))) {
            $roles[] = self::ROLE_ADMIN;
        }

        if (in_array($this->getLiikmedId(), $superAdmins)) {
            $roles[] = self::ROLE_SUPER_ADMIN;
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

    /**
     * @return array
     */
    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'roles' => $this->getRoles(),
        ];
    }
}
