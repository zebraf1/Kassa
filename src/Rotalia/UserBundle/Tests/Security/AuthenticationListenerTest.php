<?php

namespace Rotalia\UserBundle\Tests\Security;

use PropelException;
use Rotalia\APIBundle\Tests\Controller\WebTestCase;
use Rotalia\UserBundle\Model\UserQuery;
use Rotalia\UserBundle\Security\AuthenticationListener;
use Rotalia\UserBundle\Security\RotaliaPasswordEncoder;
use Rotalia\UserBundle\Security\RotaliaPasswordEncoderPropel;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class AuthenticationListenerTest extends WebTestCase
{
    private $providerKey = 'test';

    /**
     * @param bool $canMigrate
     * @return AuthenticationListener
     * @throws \Exception
     */
    private function getListener(bool $canMigrate): AuthenticationListener
    {
        $encoders = [];
        foreach (RotaliaPasswordEncoder::plugins as $plugin) {
            $encoders[$plugin] = new RotaliaPasswordEncoderPropel($plugin);
        }
        $factory = new EncoderFactory($encoders);
        return new AuthenticationListener($factory, $canMigrate);
    }

    private function getEvent(): AuthenticationEvent
    {
        $token = new UsernamePasswordToken('user1', 'test123', $this->providerKey);
        $user = UserQuery::create()->findOneByUsername('user1');
        $token->setUser($user);
        return new AuthenticationEvent($token);
    }

    /**
     * @throws PropelException
     */
    public function testOnAuthenticationSuccessMigrate(): void
    {
        try {
            $this->getListener(true)->onAuthenticationSuccess($this->getEvent());
            $user = UserQuery::create()->findOneByUsername('user1');
            // Last login is updated
            $this->assertNotEquals('2015-04-09 20:13:15', $user->getLastlogin('Y-m-d H:i:s'));
            // Password is migrated to native plugin
            $this->assertEquals(RotaliaPasswordEncoder::PLUGIN_NATIVE_PASSWORD, $user->getPlugin());
            $this->assertEquals('*676243218923905CF94CB52A3C9D3EB30CE8E20D', $user->getPassword());
        } finally {
            self::loadFixtures(); // cleanup
        }
    }

    /**
     * @throws PropelException
     */
    public function testOnAuthenticationSuccessMigrationDisabled(): void
    {
        try {
            $this->getListener(false)->onAuthenticationSuccess($this->getEvent());
            $user = UserQuery::create()->findOneByUsername('user1');
            // Last login is updated
            $this->assertNotEquals('2015-04-09 20:13:15', $user->getLastlogin('Y-m-d H:i:s'));
            // Migration will not be done (canMigrate = false)
            $this->assertEquals(RotaliaPasswordEncoder::PLUGIN_PLAIN, $user->getPlugin());
            $this->assertEquals('test123', $user->getPassword());
        } finally {
            self::loadFixtures(); // cleanup
        }
    }
}
