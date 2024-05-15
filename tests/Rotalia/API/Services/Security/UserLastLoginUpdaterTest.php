<?php

namespace Tests\Rotalia\API\Services\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Rotalia\API\Services\Security\UserLastLoginUpdater;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

#[CoversClass(UserLastLoginUpdater::class)]
class UserLastLoginUpdaterTest extends TestCase
{
    public function testOnAuthenticationSuccess(): void
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['persist'])
            ->getMock();
        $updater = new UserLastLoginUpdater($manager);
        $user = new User();
        $token = new TestBrowserToken([], $user);
        $event = new AuthenticationSuccessEvent($token);
        $updater->onAuthenticationSuccess($event);
        $this->assertNotNull($user->getLastlogin());
    }
}
