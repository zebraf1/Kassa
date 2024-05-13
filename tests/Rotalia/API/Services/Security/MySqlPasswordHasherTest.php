<?php

namespace Tests\Rotalia\API\Services\Security;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Rotalia\API\Services\Security\MySqlPasswordHasher;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(MySqlPasswordHasher::class)]
class MySqlPasswordHasherTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * @throws Exception
     */
    public function testHash(): void
    {
        $hasher = new MySqlPasswordHasher($this->entityManager);
        $hash = $hasher->hash('plainPassword');
        $this->assertEquals('*018CABA3AB8FC3C462AAF9DD5BC6F59DCB99B313', $hash);
    }

    /**
     * @throws Exception
     */
    public function testVerify(): void
    {
        $hasher = new MySqlPasswordHasher($this->entityManager);
        $isValid = $hasher->verify('*018CABA3AB8FC3C462AAF9DD5BC6F59DCB99B313', 'plainPassword');
        $this->assertTrue($isValid);
    }

    public function testNeedsRehash(): void
    {
        $hasher = new MySqlPasswordHasher($this->entityManager);
        $needsRehash = $hasher->needsRehash('*018CABA3AB8FC3C462AAF9DD5BC6F59DCB99B313');
        $this->assertFalse($needsRehash);
    }
}
