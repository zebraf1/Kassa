<?php

namespace Tests\Helpers;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class should extend KernelTestCase or provide Kernel
 * @property ?KernelInterface $kernel
 */
trait EntityManagerAwareTestCase
{
    protected ?EntityManagerInterface $entityManager;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$kernel) {
            self::bootKernel();
        }

        $this->entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }
}
