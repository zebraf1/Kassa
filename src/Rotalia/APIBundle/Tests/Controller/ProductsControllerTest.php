<?php

namespace Rotalia\APIBundle\Tests\Controller;
use Rotalia\APIBundle\Classes\XClassifier;
use Rotalia\APIBundle\Model\ProductGroupQuery;
use Rotalia\APIBundle\Model\ProductQuery;

/**
 * Class ProductsControllerTest
 * @package Rotalia\APIBundle\Tests\Controller
 */
class ProductsControllerTest extends WebTestCase
{
    /**
     * Test list without login
     */
    public function testGetListUnauthorised()
    {
        static::$client->request('GET', '/api/products/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied.', $result->message);
    }

    /**
     * Test list without filters
     */
    public function testGetList()
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(4, $result->data->products);
    }

    // Test filters

    public function testGetListFilterName()
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products/', ['name' => 'Premium']);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(1, $result->data->products);

        $product = reset($result->data->products);
        $this->assertEquals('A le Coq Premium', $product->name);
    }

    public function testGetListFilterProductCode()
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products/', ['productCode' => '12345678']);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(1, $result->data->products);

        $product = reset($result->data->products);
        $this->assertEquals(['12345678'], $product->productCodes);
    }

    public function testGetListFilterProductGroupId()
    {
        $this->loginSimpleUser();

        $productGroup = ProductGroupQuery::create()->findOneByName('Õlu');
        static::$client->request('GET', '/api/products/', ['productGroupId' => $productGroup->getId()]);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(2, $result->data->products);

        foreach ($result->data->products as $product) {
            $this->assertEquals($productGroup->getId(), $product->productGroupId);
        }
    }

    public function testGetListFilterActive()
    {
        $this->loginSimpleUser(); // Convent_6

        static::$client->request('GET', '/api/products/', ['active' => 1]);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(3, $result->data->products);

        foreach ($result->data->products as $product) {
            $this->assertEquals(XClassifier::STATUS_ACTIVE, $product->status);
        }
    }

    public function testGetListFilterInactive()
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/products/', ['active' => 0]);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->products);
        $this->assertCount(1, $result->data->products);

        foreach ($result->data->products as $product) {
            $this->assertEquals(XClassifier::STATUS_DISABLED, $product->status);
        }
    }

    public function testGetListPagination()
    {
        $this->testGetListPaginationForLimit(0);
        $this->testGetListPaginationForLimit(1);
        $this->testGetListPaginationForLimit(2);
        $this->testGetListPaginationForLimit(3);
        $this->testGetListPaginationForLimit(4);
    }

    public function testGetListPaginationForLimit($limit = 100)
    {
        $this->loginSimpleUser();

        $totalCount = ProductQuery::create()->count();
        $pages = 999; // initial value
        $page = 1;

        while ($page <= $pages) {
            static::$client->request('GET', '/api/products/', ['page' => $page, 'limit' => $limit]);
            $response = static::$client->getResponse();
            $result = json_decode($response->getContent());
            $this->assertEquals(200, $response->getStatusCode());

            if ($pages === 999) {
                $pages = (int)$result->data->pages;
            }

            $this->assertEquals($page, $result->data->page);

            if ($page < $pages) {
                $this->assertCount(
                    $limit,
                    $result->data->products,
                    'Incorrect result count for page '.$page.' with limit ' . $limit
                );
            } else {
                $this->assertCount(
                    $totalCount - ($page - 1) * $limit,
                    $result->data->products,
                    'Incorrect result count for page '.$page.' with limit ' . $limit
                );
            }

            $page++;
        }
    }
}
