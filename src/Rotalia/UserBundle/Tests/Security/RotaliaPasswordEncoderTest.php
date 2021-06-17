<?php

namespace Rotalia\UserBundle\Tests\Security;

use Exception;
use Rotalia\APIBundle\Tests\Controller\WebTestCase;
use Rotalia\UserBundle\Security\RotaliaPasswordEncoder;

class RotaliaPasswordEncoderTest extends WebTestCase
{
    /**
     * @param $raw
     * @param $salt
     * @param $expected
     * @dataProvider providerEncodePassword
     */
    public function testEncodePassword($raw, $salt, $expected): void
    {
        $encoder = new RotaliaPasswordEncoder();
        try {
            $result = $encoder->encodePassword($raw, $salt);
            if ($expected instanceof Exception) {
                throw new Exception('Expected exception not thrown');
            }
            $this->assertSame($expected, $result);
            $this->assertTrue($encoder->isPasswordValid($result, $raw, $salt));
        } catch (Exception $e) {
            $this->assertEquals($expected instanceof Exception ? $expected->getMessage() : $expected, $e->getMessage());
        }
    }

    public function providerEncodePassword(): array
    {
        return [
            'plain' => [
                'plain_value', RotaliaPasswordEncoder::PLUGIN_PLAIN, 'plain_value'
            ],
            'native' => [
                'plain_value', RotaliaPasswordEncoder::PLUGIN_NATIVE_PASSWORD, '*A2AE0AA3A98DECD4B8C1EF3FA30C9D8D5ED412FB'
            ],
            'old' => [
                // OLD_PASSWORD plugin is not installed, only in production
                'plain_value', RotaliaPasswordEncoder::PLUGIN_OLD_PASSWORD, new Exception(
                    'SQLSTATE[42000]: Syntax error or access violation: 1305 FUNCTION kassa_test.OLD_PASSWORD does not exist'
                )
            ],
            'other' => [
                'plain_value', 'unsupported', new Exception('Unsupported plugin: unsupported')
            ],
        ];
    }
}
