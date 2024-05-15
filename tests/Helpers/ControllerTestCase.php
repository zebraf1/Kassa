<?php

namespace Tests\Helpers;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerTestCase extends WebTestCase
{
    protected static ?KernelBrowser $client;

    protected function setUp(): void
    {
        self::$client = static::createClient();
        self::$client->followRedirects();
    }

    public static function setUpBeforeClass(): void
    {
        // TODO: load fixtures
    }

    /**
     * @param string $username
     * @return User
     */
    protected function login(string $username): User
    {
        $client = static::$client;

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['username' => $username]);
        $this->assertNotNull($testUser);
        // simulate $testUser being logged in
        $client->loginUser($testUser);

        return $testUser;
    }

    protected function loginSuperAdmin(): User
    {
        return $this->login('user1');
    }

    protected function loginAdmin(): User
    {
        return $this->login('user2');
    }

    protected function loginSimpleUser(): User
    {
        return $this->login('user3');
    }
}
