<?php

namespace Rotalia\API\Services\Security;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class MySqlPasswordHasher implements PasswordHasherInterface
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->entityManager = $manager;
    }

    /**
     * @throws Exception
     */
    public function hash(#[\SensitiveParameter] string $plainPassword): string
    {
        // Note: OLD_PASSWORD does not work since MySQL 5.7.5, but Works on MariaDB
        $sth = $this->entityManager
            ->getConnection()
            ->executeQuery('SELECT PASSWORD(:password)', ['password' => $plainPassword])
        ;
        return $sth->fetchOne();
    }

    /**
     * @throws Exception
     */
    public function verify(string $hashedPassword, #[\SensitiveParameter] string $plainPassword): bool
    {
        return hash_equals($hashedPassword, $this->hash($plainPassword));
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }
}
