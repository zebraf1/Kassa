<?php

use Tests\Helpers\ControllerTestCase;

class ProductGroupsControllerTest extends ControllerTestCase
{
    /**
     * Test list without login
     */
    public function testGetListUnauthorised(): void
    {
        static::$client->request('GET', '/api/productGroups');
        $response = static::$client->getResponse();
        $result = $this->getResponseBodyJson();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Ligipääs puudub', $result['message']);
    }

    /**
     * Test list without filters
     */
    public function testGetList(): void
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/productGroups/');
        $response = static::$client->getResponse();
        $result = $this->getResponseBodyJson();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result['data']['productGroups']);
        $this->assertCount(2, $result['data']['productGroups']);
    }
}
