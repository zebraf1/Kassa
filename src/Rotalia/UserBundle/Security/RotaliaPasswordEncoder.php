<?php

namespace Rotalia\UserBundle\Security;


use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class RotaliaPasswordEncoder implements PasswordEncoderInterface
{
    public const PLUGIN_PLAIN = 'plain';
    public const PLUGIN_OLD_PASSWORD = 'plain';
    protected $plugin;

    public function __construct($plugin = self::PLUGIN_OLD_PASSWORD)
    {
        $this->plugin = $plugin;
    }

    /**
     * {@inheritdoc}
     */
    public function encodePassword($raw, $salt)
    {
        if ($this->plugin === self::PLUGIN_PLAIN) {
            return $raw;
        }

        $con = \Propel::getConnection();
        $sql = "SELECT OLD_PASSWORD(".\Propel::getConnection()->quote($raw).")";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return hash_equals($encoded, $this->encodePassword($raw, $salt));
    }
}
