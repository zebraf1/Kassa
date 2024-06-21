<?php

namespace Tests\Rotalia\API\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use Rotalia\API\Controller\ProductGroupsController;
use Tests\Helpers\ControllerTestCase;

#[CoversClass(ProductGroupsController::class)]
class ProductGroupsControllerTest extends ControllerTestCase
{
    /**
     * Test list without login
     */
    public function testListUnauthorised(): void
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
    public function testList(): void
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/productGroups');
        $response = static::$client->getResponse();
        $result = $this->getResponseBodyJson();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result['data']['productGroups']);
        $this->assertCount(2, $result['data']['productGroups']);
    }

    /**
     * Test adding new ProductGroup
     */
    public function testCreateSuccess(): void
    {
        $this->loginAdmin();

        static::$client->request('POST', '/api/productGroups', [
            'name' => 'Uus Grupp',
            'seq' => '33',
        ]);

        $response = static::$client->getResponse();

        $this->assertResponseEqualsJsonPath('Uus Grupp', 'data.productGroup.name');
        $this->assertResponseEqualsJsonPath(33, 'data.productGroup.seq');
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testBadRequest(): void
    {
        $this->loginAdmin();

        static::$client->request('POST', '/api/productGroups', [
            'name' => ''
        ]);

        $response = static::$client->getResponse();

        $this->assertResponseEqualsJsonPath('Nimi peab olema vähemalt 1 tähemärk', 'message');
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testPostAccessDenied(): void
    {
        $this->loginSimpleUser();

        // Update group name and seq
        static::$client->request('POST', '/api/productGroups', [
            'name' => 'Test',
            'seq' => 3,
        ]);

        $response = static::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());

        $this->assertResponseEqualsJsonPath('Ligipääs puudub', 'message');
    }
}
