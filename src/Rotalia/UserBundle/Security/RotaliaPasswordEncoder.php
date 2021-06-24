<?php

namespace Rotalia\UserBundle\Security;

use Exception;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

abstract class RotaliaPasswordEncoder implements PasswordEncoderInterface
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
     * @param string $plugin
     * @throws Exception
     */
    public function __construct(string $plugin)
    {
        if (!in_array($plugin, self::plugins, true)) {
            throw new Exception('Unsupported plugin: ' . $plugin);
        }

        $this->plugin = $plugin;
    }

    public function getPlugin(): string
    {
        return $this->plugin;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function encodePassword($raw, $salt): string
    {
        switch ($this->getPlugin()) {
            case self::PLUGIN_OLD_PASSWORD:
                return $this->runQuery(sprintf("SELECT OLD_PASSWORD(%s)", $this->escapeString($raw)));
            case self::PLUGIN_NATIVE_PASSWORD:
                return $this->runQuery(sprintf("SELECT PASSWORD(%s)", $this->escapeString($raw)));
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

    /**
     * Escape string to be SQL-injection safe
     * @param string $string
     * @return string
     */
    abstract protected function escapeString(string $string): string;

    /**
     * Run SQL query and return encoded value
     * @param string $sql
     * @return string
     */
    abstract protected function runQuery(string $sql): string;
}
