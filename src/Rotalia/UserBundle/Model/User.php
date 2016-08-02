<?php

namespace Rotalia\UserBundle\Model;

use Rotalia\UserBundle\Model\om\BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends BaseUser implements UserInterface
{
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_USER = 'ROLE_USER';
    const USER_RIGHT_KASSA_ADMIN = 'KASSAADMIN';
    const USER_RIGHT_KASSA_SUPER_ADMIN = 'KASSASUPER';

    public function getRoles()
    {
        $roles = [self::ROLE_USER];

        foreach ($this->getUserRights() as $userRight) {
            switch($userRight->getRoleName()) {
                case self::USER_RIGHT_KASSA_SUPER_ADMIN:
                    // Role hierarchy
                    $roles[] = self::ROLE_SUPER_ADMIN;
                    $roles[] = self::ROLE_ADMIN;
                    break;
                case self::USER_RIGHT_KASSA_ADMIN:
                    $roles[] = self::ROLE_ADMIN;
                    break;
                default:
                    break;
            }
        }

        return array_unique($roles);
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
