<?php

namespace Rotalia\UserBundle\Security;

use Exception;
use Propel;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class RotaliaPasswordEncoder implements PasswordEncoderInterface
{
    public const PLUGIN_PLAIN = 'plain';
    public const PLUGIN_OLD_PASSWORD = 'mysql_old_password';
    public const PLUGIN_NATIVE_PASSWORD = 'mysql_native_password';

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function encodePassword($raw, $salt)
    {
        switch ($salt) {
            case self::PLUGIN_OLD_PASSWORD:
                $con = Propel::getConnection();
                $sql = "SELECT OLD_PASSWORD(". Propel::getConnection()->quote($raw).")";
                $stmt = $con->query($sql);
                return $stmt->fetchColumn();
            case self::PLUGIN_NATIVE_PASSWORD:
                $con = Propel::getConnection();
                $sql = "SELECT PASSWORD(". Propel::getConnection()->quote($raw).")";
                $stmt = $con->query($sql);
                return $stmt->fetchColumn();
            case self::PLUGIN_PLAIN:
                return $raw;
            default:
                throw new Exception('Unsupported plugin: ' . $salt);
        }
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function isPasswordValid($encoded, $raw, $salt): bool
    {
        return hash_equals($encoded, $this->encodePassword($raw, $salt));
    }
}
