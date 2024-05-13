<?php

namespace Tests\Rotalia\API\Services\Security;

use PHPUnit\Framework\Attributes\CoversClass;
use Rotalia\API\Services\Security\PlainTextPasswordHasher;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(PlainTextPasswordHasher::class)]
class PlainTextPasswordHasherTest extends KernelTestCase
{
    public function testHash(): void
    {
        $hasher = new PlainTextPasswordHasher();
        $hash = $hasher->hash('plainPassword');
        $this->assertSame('plainPassword', $hash);
    }

    public function testVerify(): void
    {
        $hasher = new PlainTextPasswordHasher();
        $this->assertTrue($hasher->verify('plainPassword', 'plainPassword'));
        $this->assertFalse($hasher->verify('plainPassword1', 'plainPassword'));
    }

    public function testNeedsRehash(): void
    {
        $hasher = new PlainTextPasswordHasher();
        $needsRehash = $hasher->needsRehash('plainPassword1');
        $this->assertFalse($needsRehash);
    }
}
