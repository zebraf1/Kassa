<?php

namespace Rotalia\APIBundle\Tests\Controller;


class ProductGroupsControllerTest extends WebTestCase
{
    /**
     * Test list without login
     */
    public function testGetListUnauthorised()
    {
        static::$client->request('GET', '/api/productGroups/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied', $result->message);
    }

    /**
     * Test list without filters
     */
    public function testGetList()
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/productGroups/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->productGroups);
        $this->assertCount(2, $result->data->productGroups);
    }
}
