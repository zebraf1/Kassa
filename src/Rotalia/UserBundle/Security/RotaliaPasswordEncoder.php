<?php

namespace Rotalia\UserBundle\Security;

use Exception;
use PDO;
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
    private $pdo;

    /**
     * RotaliaPasswordEncoder constructor.
     * @param string $plugin
     * @param PDO $pdo
     * @throws Exception
     */
    public function __construct(string $plugin, PDO $pdo)
    {
        if (!in_array($plugin, self::plugins, true)) {
            throw new Exception('Unsupported plugin: ' . $plugin);
        }

        $this->plugin = $plugin;
        $this->pdo = $pdo;
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
                return $this->runQuery("SELECT OLD_PASSWORD(:password)", ['password' => $raw]);
            case self::PLUGIN_NATIVE_PASSWORD:
                return $this->runQuery("SELECT PASSWORD(:password)", ['password' => $raw]);
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
     * Run SQL query and return encoded value
     * @param string $sql
     * @param array $params
     * @return string
     */
    protected function runQuery(string $sql, array $params = []): string
    {
        $sth = $this->pdo->prepare($sql);
        $sth->execute($params);
        return $sth->fetchColumn();
    }
}
