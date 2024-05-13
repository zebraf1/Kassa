<?php

namespace Rotalia\API\Services\Security;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * Used for test environments
 */
class PlainTextPasswordHasher implements PasswordHasherInterface
{

    public function hash(#[\SensitiveParameter] string $plainPassword): string
    {
        return $plainPassword;
    }

    public function verify(string $hashedPassword, #[\SensitiveParameter] string $plainPassword): bool
    {
        return $hashedPassword === $plainPassword;
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }
}
