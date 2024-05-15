<?php

namespace Tests\Rotalia\API\Services\Security;

use App\Entity\User;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Rotalia\API\Services\Security\MySqlOldPasswordHasher;
use Rotalia\API\Services\Security\MySqlPasswordHasher;

#[CoversClass(MySqlOldPasswordHasher::class)]
#[UsesClass(MySqlPasswordHasher::class)]
class MySqlOldPasswordHasherTest extends TestCase
{
    private ?EntityManager $entityManager;
    private ?User $currentUser;

    /**
     * Note: MySQL OLD_PASSWORD is not available since v5.7.5
     * This means that normal test environment cannot run it directly on the database
     * So we have to mock the actual database query
     *
     * @return void
     */
    protected function setUp(): void
    {
        $result = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOne'])
            ->getMock();
        $result->method('fetchOne')->willReturn('hashed123pass');
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['executeQuery'])
            ->getMock();
        $connection->method('executeQuery')->willReturn($result);
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection', 'persist'])
            ->getMock();
        $entityManager->method('getConnection')->willReturn($connection);
        $this->entityManager = $entityManager;

        $this->currentUser = new User();
        $this->currentUser
            ->setUsername('passwordMigration')
            ->setPlugin(User::PLUGIN_OLD_PASSWORD)
            ->setPassword('hashed123pass')
            ->setLiikmedId(1)
            ->setLastlogin(new DateTime())
        ;
    }

    /**
     * @throws Exception
     */
    public function testHash(): void
    {
        $hasher = new MySqlOldPasswordHasher($this->entityManager, $this->currentUser);
        $hash = $hasher->hash('plainPassword');
        $this->assertEquals('hashed123pass', $hash);
    }

    /**
     * @throws Exception
     */
    public function testVerifyMigrate(): void
    {
        $hasher = new MySqlOldPasswordHasher($this->entityManager, $this->currentUser);
        $isValid = $hasher->verify('hashed123pass', 'plainPassword');
        $this->assertTrue($isValid);
        $this->assertSame(User::PLUGIN_NATIVE_PASSWORD, $this->currentUser->getPlugin());
        // Mock EntityManager will return the same value from MySql native hasher
        $this->assertSame('hashed123pass', $this->currentUser->getPassword());
    }
}
