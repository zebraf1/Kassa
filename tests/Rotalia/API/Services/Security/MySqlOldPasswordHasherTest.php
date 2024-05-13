<?php

namespace Tests\Rotalia\API\Services\Security;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Rotalia\API\Services\Security\MySqlOldPasswordHasher;

#[CoversClass(MySqlOldPasswordHasher::class)]
class MySqlOldPasswordHasherTest extends TestCase
{
    private ?EntityManager $entityManager;

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
            ->onlyMethods(['getConnection'])
            ->getMock()
        ;
        $entityManager->method('getConnection')->willReturn($connection);
        $this->entityManager = $entityManager;
    }

    /**
     * @throws Exception
     */
    public function testHash(): void
    {
        $hasher = new MySqlOldPasswordHasher($this->entityManager);
        $hash = $hasher->hash('plainPassword');
        $this->assertEquals('hashed123pass', $hash);
    }

    public function testNeedsRehash(): void
    {
        $hasher = new MySqlOldPasswordHasher($this->entityManager);
        $needsRehash = $hasher->needsRehash('oldPassword');
        $this->assertTrue($needsRehash);
    }
}
