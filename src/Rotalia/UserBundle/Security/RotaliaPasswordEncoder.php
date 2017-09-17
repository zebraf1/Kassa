<?php

namespace Rotalia\UserBundle\Security;


use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Util\StringUtils;

class RotaliaPasswordEncoder implements PasswordEncoderInterface
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function encodePassword($raw, $salt)
    {
        $con = \Propel::getConnection();
        $sql = "SELECT OLD_PASSWORD(".\Propel::getConnection()->quote($raw).")";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $encoded = $stmt->fetchColumn(0);

        return $encoded;
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return hash_equals($encoded, $this->encodePassword($raw, $salt));
    }
}
