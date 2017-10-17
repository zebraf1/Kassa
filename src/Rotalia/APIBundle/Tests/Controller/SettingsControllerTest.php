<?php

namespace Rotalia\APIBundle\Tests\Controller;

/**
 * Class SettingsControllerTest
 * @package Rotalia\APIBundle\Tests\Controller
 */
class SettingsControllerTest extends WebTestCase
{
    /**
     * Test setting list with login
     */
    public function testGetSettings()
    {
        $this->login('user1', 'test123');

        $client = self::$client;

        $client->request('GET', '/api/settings/');

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $result = json_decode($response->getContent());

        $this->assertNotEmpty($result->data->activeConvents);

        $this->assertCount(2, $result->data->activeConvents);

        $expectedActiveConvents = ['Tallinn', 'Tartu'];

        foreach ($result->data->activeConvents as $convent) {
            $this->assertTrue(in_array($convent->name, $expectedActiveConvents));
            $this->assertTrue(!empty($convent->settings));
            // TODO - must use Fixture classes for settings
//            $this->assertTrue(!empty($convent->settings->bankAccountOwner), json_encode($convent->settings));
//            $this->assertTrue(!empty($convent->settings->bankAccountIban));
        }
    }

    /**
     * Test setting list without login
     */
    public function testGetSettingsAnonymous()
    {
        $client = self::$client;

        $client->request('GET', '/api/settings/');

        $response = $client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());

        $result = json_decode($response->getContent());

        $this->assertEquals('Access Denied.', $result->message);
    }
}
