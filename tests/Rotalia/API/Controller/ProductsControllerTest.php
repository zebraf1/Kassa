<?php

namespace Tests\Rotalia\API\Controller;

use App\Entity\Product;
use App\Entity\ProductGroup;
use Hautelook\AliceBundle\PhpUnit\FixtureStore;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Rotalia\API\Controller\DefaultController;
use Rotalia\API\Controller\ProductsController;
use Tests\Helpers\ControllerTestCase;
use Tests\Helpers\EntityManagerAwareTestCase;

#[CoversClass(ProductsController::class)]
#[UsesClass(DefaultController::class)]
class ProductsControllerTest extends ControllerTestCase
{
    use EntityManagerAwareTestCase;
    use RefreshDatabaseTrait;

    public function testListUnauthorized(): void
    {
        static::$client->request('GET', '/api/products');
        $response = static::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertResponseEqualsJsonPath('Ligipääs puudub', 'message');
    }

    public function testListSuccess(): void
    {
        $this->loginSimpleUser(); // Tallinn convent

        static::$client->request('GET', '/api/products');
        $response = static::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseEqualsJsonPath('A le Coq Aleksander', 'data.products.0.name');
        $this->assertResponseEqualsJsonPath(Product::STATUS_ACTIVE, 'data.products.0.status');
    }

    public function testListSuccessOtherConvent(): void
    {
        $this->loginSimpleUser();
        $tartu = FixtureStore::getFixtures()['Convent_7'];

        static::$client->request('GET', '/api/products', [
            'conventId' => $tartu->getId(),
        ]);
        $response = static::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseEqualsJsonPath('A le Coq Aleksander', 'data.products.0.name');
        // Status is different for Tartu
        $this->assertResponseEqualsJsonPath(Product::STATUS_DISABLED, 'data.products.0.status');
    }

    public function testListFilterActive(): void
    {
        $this->loginSimpleUser();
        $tartu = FixtureStore::getFixtures()['Convent_7'];

        static::$client->request('GET', '/api/products', ['active' => 1, 'conventId' => $tartu->getId()]);
        $response = static::$client->getResponse();

        // Aleksander and Pilsner not returned for Tartu
        $this->assertResponseEqualsJsonPath('A le Coq Premium', 'data.products.0.name');
        $this->assertResponseEqualsJsonPath('Tuc Küpsis', 'data.products.1.name');
        $this->assertResponseCountJsonPath(2, 'data.products');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testListFilterInactive(): void
    {
        $this->loginSimpleUser();
        $tartu = FixtureStore::getFixtures()['Convent_7'];

        static::$client->request('GET', '/api/products', ['active' => 'false', 'conventId' => $tartu->getId()]);
        $response = static::$client->getResponse();

        // Premium and Tuc not returned for Tartu
        $this->assertResponseEqualsJsonPath('A le Coq Aleksander', 'data.products.0.name');
        $this->assertResponseEqualsJsonPath('A le Coq Pilsner', 'data.products.1.name');
        $this->assertResponseCountJsonPath(2, 'data.products');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testListFilterName(): void
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products', ['name' => 'Premium']);
        $response = static::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseEqualsJsonPath('A le Coq Premium', 'data.products.0.name');
    }

    public function testListFilterProductCode(): void
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products', ['productCode' => '12345678']);
        $response = static::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertResponseEqualsJsonPath(['12345678'], 'data.products.0.productCodes');
    }

    public function testGetListFilterProductGroupId(): void
    {
        $this->loginSimpleUser();

        /** @var ProductGroup $beer */
        $beer = FixtureStore::getFixtures()['ProductGroup_1'];

        static::$client->request('GET', '/api/products', ['productGroupId' => $beer->getId()]);
        $response = static::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertResponseEqualsJsonPath($beer->getId(), 'data.products.0.productGroupId');
        $this->assertResponseEqualsJsonPath($beer->getId(), 'data.products.1.productGroupId');
    }
}
