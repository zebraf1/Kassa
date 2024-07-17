<?php

namespace Tests\Rotalia\API\Controller;

use App\Entity\ProductGroup;
use Hautelook\AliceBundle\PhpUnit\FixtureStore;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Rotalia\API\Controller\ProductGroupsController;
use Tests\Helpers\ControllerTestCase;
use Tests\Helpers\EntityManagerAwareTestCase;

#[CoversClass(ProductGroupsController::class)]
class ProductGroupsControllerTest extends ControllerTestCase
{
    use EntityManagerAwareTestCase;
    use RefreshDatabaseTrait;

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
            'productGroup' => [
                'name' => 'Uus Grupp',
                'seq' => '33',
            ],
        ]);

        $response = static::$client->getResponse();

        $this->assertResponseEqualsJsonPath('Uus Grupp', 'data.productGroup.name');
        $this->assertResponseEqualsJsonPath(33, 'data.productGroup.seq');
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCreateBadRequest1(): void
    {
        $this->loginSuperAdmin();

        static::$client->request('POST', '/api/productGroups', [
            'productGroup' => [
                'name' => ' ',
            ],
        ]);

        $response = static::$client->getResponse();

        $this->assertResponseEqualsJsonPath('Sisesta nimi', 'message');
        $this->assertResponseEqualsJsonPath('Sisesta nimi', 'data.name');
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateBadRequest2(): void
    {
        $this->loginSuperAdmin();

        static::$client->request('POST', '/api/productGroups', [
            'productGroup' => [
                'name' => str_pad('', 101, 'a'),
            ],
        ]);

        $response = static::$client->getResponse();

        $this->assertResponseEqualsJsonPath('Nimi peab olema kuni 100 tähemärki', 'message');
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateBadRequest3(): void
    {
        $this->loginSuperAdmin();

        static::$client->request('POST', '/api/productGroups');

        $response = static::$client->getResponse();

        $this->assertResponseEqualsJsonPath('Andmete salvestamine ebaõnnestus', 'message');
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateAccessDenied(): void
    {
        $this->loginSimpleUser();

        // Update group name and seq
        static::$client->request('POST', '/api/productGroups', [
            'productGroup' => [
                'name' => 'Test',
                'seq' => 3,
            ],
        ]);

        $response = static::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());

        $this->assertResponseEqualsJsonPath('Ligipääs puudub', 'message');
    }

    public function testUpdateSuccess(): void
    {
        $this->loginAdmin();

        /** @var ProductGroup $group */
        $group = FixtureStore::getFixtures()['ProductGroup_1'];

        // Update group name and seq
        static::$client->request('PATCH', '/api/productGroups/'.$group->getId(), [
            'productGroup' => [
                'name' => 'Test',
                'seq' => 3,
            ]
        ]);

        $response = static::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertResponseEqualsJsonPath($group->getId(), 'data.productGroup.id');
        $this->assertResponseEqualsJsonPath('Test', 'data.productGroup.name');
        $this->assertResponseEqualsJsonPath(3, 'data.productGroup.seq');
    }

    public function testUpdateNotFound(): void
    {
        $this->loginAdmin();

        // Update group name and seq
        static::$client->request('PATCH', '/api/productGroups/8888888', [
            'productGroup' => [
                'name' => 'Test',
                'seq' => 3,
            ]
        ]);

        $response = static::$client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

        $this->assertResponseEqualsJsonPath('Tootegruppi ei leitud', 'message');
    }
}
