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

    // These plugins are handled in encodePassword() which defaults to plain
    public const plugins = [
        self::PLUGIN_PLAIN,
        self::PLUGIN_OLD_PASSWORD,
        self::PLUGIN_NATIVE_PASSWORD,
    ];

    private $plugin;

    /**
     * RotaliaPasswordEncoder constructor.
     * @param string|null $plugin
     * @throws Exception
     */
    public function __construct(string $plugin = null)
    {
        if (!in_array($plugin, self::plugins, true)) {
            throw new Exception('Unsupported plugin: ' . $plugin);
        }

        $this->plugin = $plugin;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function encodePassword($raw, $salt)
    {
        switch ($this->plugin) {
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
            default: // Plugin value can only contain predefined values
                return $raw;
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
