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

    public function getRoles(): array
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

    /**
     * Each plugin must be defined in the security.encoders configuration
     * @return string
     */
    public function getEncoderName(): string
    {
        return $this->getPlugin();
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }
}
