<?php

namespace Rotalia\APIBundle\Tests\Controller;

/**
 * Class PurchasesControllerTest
 * @package Rotalia\APIBundle\Tests\Controller
 */
class PurchasesControllerTest extends WebTestCase
{
    /**
     * Test list without login
     */
    public function testListUnauthorised()
    {
        static::$client->request('GET', '/api/purchase/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Ostude nägemiseks peab olema kas sisse logitud või kasutama müügipunkti', $result->message);
    }

    /**
     * Test list without filters for admin
     */
    public function testListAdmin()
    {
        $this->loginAdmin();

        static::$client->request('GET', '/api/purchase/');
        $response = static::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test list without filters for super admin
     */
    public function testListSuper()
    {
        $this->loginSuperAdmin();

        static::$client->request('GET', '/api/purchase/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->purchases);
        $this->assertCount(3, $result->data->purchases);
    }

    /**
     * Test list with convent filter as regular user
     */
    public function testListFilterConvent()
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/purchase/', ['conventId' => '7']);
        $response = static::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test if limits work correctly
     */
    public function testListLimit()
    {
        $this->loginSuperAdmin();

        static::$client->request('GET', '/api/purchase/', ['limit' => 2]);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->purchases);
        $this->assertCount(2, $result->data->purchases);
    }
}