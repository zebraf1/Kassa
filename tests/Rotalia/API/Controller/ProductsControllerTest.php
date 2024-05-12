<?php

namespace Tests\Rotalia\API\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use Rotalia\API\Controller\DefaultController;
use Rotalia\API\Controller\ProductsController;
use Tests\Helpers\ControllerTestCase;

#[CoversClass(ProductsController::class)]
#[CoversClass(DefaultController::class)] // json method
class ProductsControllerTest extends ControllerTestCase
{
    public function testListSuccess(): void
    {
//        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(4, $result->data->products);
    }

    public function testListFilterName(): void
    {
//        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products', ['name' => 'Premium']);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(1, $result->data->products);

        $product = reset($result->data->products);
        $this->assertEquals('A le Coq Premium', $product->name);
    }

    public function testListFilterProductCode(): void
    {
//        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products', ['productCode' => '12345678']);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(1, $result->data->products);

        $product = reset($result->data->products);
        $this->assertEquals(['12345678'], $product->productCodes);
    }

    public function testGetListFilterProductGroupId()
    {
//        $this->loginSimpleUser();

        $id = 996;

        static::$client->request('GET', '/api/products', ['productGroupId' => $id]);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(2, $result->data->products);

        foreach ($result->data->products as $product) {
            $this->assertEquals($id, $product->productGroupId);
        }
    }
}
