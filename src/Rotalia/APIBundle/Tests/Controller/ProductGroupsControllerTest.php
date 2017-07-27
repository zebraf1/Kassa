<?php

namespace Rotalia\APIBundle\Tests\Controller;


use Rotalia\InventoryBundle\Model\ProductGroupQuery;

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
        $this->assertEquals('Access Denied.', $result->message);
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

    /**
     * Test adding new ProductGroup
     */
    public function testPost()
    {
        $this->loginAdmin();

        static::$client->request('POST', '/api/productGroups/', [
            'ProductGroupType' => [
                'name' => 'Uus Grupp',
                'seq' => 33,
            ]
        ]);

        $response = static::$client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $result = json_decode($response->getContent());
        $this->assertNotEmpty($result->data->productGroup);
        $productGroup = $result->data->productGroup;

        $this->assertEquals('Uus Grupp', $productGroup->name);
        $this->assertEquals(33, $productGroup->seq);
    }

    /**
     * Test updating ProductGroup
     */
    public function testPost403()
    {
        $this->loginSimpleUser();


        // Update group name and seq
        static::$client->request('POST', '/api/productGroups/', [
            'ProductGroupType' => [
                'name' => 'Test',
                'seq' => 3,
            ]
        ]);

        $response = static::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());

        $result = json_decode($response->getContent());
        $this->assertEquals('Tegevus vajab admin õiguseid', $result->data);
    }

    /**
     * Test updating ProductGroup
     */
    public function testPatch()
    {
        $this->loginAdmin();

        $group = ProductGroupQuery::create()->findOne();

        // Update group name and seq
        static::$client->request('PATCH', '/api/productGroups/'.$group->getId().'/', [
            'ProductGroupType' => [
                'name' => 'Test',
                'seq' => 3,
            ]
        ]);

        $response = static::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $result = json_decode($response->getContent());
        $this->assertNotEmpty($result->data->productGroup);
        $productGroup = $result->data->productGroup;

        $this->assertEquals($group->getId(), $productGroup->id);
        $this->assertEquals('Test', $productGroup->name);
        $this->assertEquals(3, $productGroup->seq);
    }

    /**
     * Test updating ProductGroup
     */
    public function testPatch403()
    {
        $this->loginSimpleUser();

        $group = ProductGroupQuery::create()->findOne();

        // Update group name and seq
        static::$client->request('PATCH', '/api/productGroups/'.$group->getId().'/', [
            'ProductGroupType' => [
                'name' => 'Test',
                'seq' => 3,
            ]
        ]);

        $response = static::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());

        $result = json_decode($response->getContent());
        $this->assertEquals('Tegevus vajab admin õiguseid', $result->data);
    }

    /**
     * Test updating ProductGroup
     */
    public function testPatch404()
    {
        $this->loginAdmin();

        // Update group name and seq
        static::$client->request('PATCH', '/api/productGroups/1/', [
            'ProductGroupType' => [
                'name' => 'Test',
                'seq' => 3,
            ]
        ]);

        $response = static::$client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

        $result = json_decode($response->getContent());
        $this->assertEquals('Tootegruppi ei leitud', $result->data);
    }
}
