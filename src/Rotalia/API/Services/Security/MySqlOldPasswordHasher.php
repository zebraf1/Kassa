<?php

namespace Rotalia\API\Services\Security;

use Doctrine\DBAL\Exception;

class MySqlOldPasswordHasher extends MySqlPasswordHasher
{
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

    public function needsRehash(string $hashedPassword): bool
    {
        return true;
    }
}
