<?php

namespace Rotalia\API\Services\Security;

use App\Entity\User;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class MySqlOldPasswordHasher extends MySqlPasswordHasher
{
    private UserInterface $currentUser;

    public function __construct(
        EntityManagerInterface $entityManager,
        #[CurrentUser] User $user)
    {
        parent::__construct($entityManager);
        $this->currentUser = $user;
    }

    /**
     * @throws Exception
     */
    public function hash(#[\SensitiveParameter] string $plainPassword): string
    {
        // Note: OLD_PASSWORD does not work since MySQL 5.7.5, but Works on MariaDB
        $result = $this->entityManager
            ->getConnection()
            ->executeQuery('SELECT OLD_PASSWORD(:password)', ['password' => $plainPassword])
        ;
        return $result->fetchOne();
    }

    /**
     * @throws Exception
     */
    public function verify(
        string $hashedPassword,
        #[\SensitiveParameter] string $plainPassword,
    ): bool
    {
        $isVerified = parent::verify($hashedPassword, $plainPassword);

        // Migrate old password to native password after successful login
        if ($isVerified) {
            $this->currentUser
                ->setPlugin(User::PLUGIN_NATIVE_PASSWORD)
                ->setPassword(parent::hash($plainPassword));
            $this->entityManager->persist($this->currentUser);
        }

        return $isVerified;
    }
}
