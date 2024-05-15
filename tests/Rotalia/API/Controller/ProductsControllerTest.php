<?php

namespace Tests\Rotalia\API\Controller;

use App\Entity\ProductGroup;
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

    /**
     * @throws \JsonException
     */
    public function testListSuccess(): void
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(4, $result->data->products);
    }

    /**
     * @throws \JsonException
     */
    public function testListFilterName(): void
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products', ['name' => 'Premium']);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(1, $result->data->products);

        $product = reset($result->data->products);
        $this->assertEquals('A le Coq Premium', $product->name);
    }

    /**
     * @throws \JsonException
     */
    public function testListFilterProductCode(): void
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products', ['productCode' => '12345678']);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(1, $result->data->products);

        $product = reset($result->data->products);
        $this->assertEquals(['12345678'], $product->productCodes);
    }

    /**
     * @throws \JsonException
     */
    public function testGetListFilterProductGroupId(): void
    {
        $this->loginSimpleUser();

        /** @var ProductGroup $beer */
        $beer = $this->entityManager->getRepository(ProductGroup::class)->findOneBy(['name' => 'Õlu']);
        $this->assertNotNull($beer);

        static::$client->request('GET', '/api/products', ['productGroupId' => $beer->getId()]);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(2, $result->data->products);

        foreach ($result->data->products as $product) {
            $this->assertEquals($beer->getId(), $product->productGroupId);
        }
    }
}
