<?php

namespace Rotalia\APIBundle\Tests\Controller;


use Rotalia\InventoryBundle\Model\Product;
use Rotalia\InventoryBundle\Model\ProductQuery;
use Rotalia\InventoryBundle\Model\Report;
use Rotalia\InventoryBundle\Model\ReportQuery;
use Rotalia\UserBundle\Model\ConventQuery;

class ReportsControllerTest extends WebTestCase
{
    public function testListUnauthorised()
    {
        static::$client->request('GET', '/api/reports/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied', $result->message);
    }

    public function testList()
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/reports/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(1, $result->data->reports);
    }

    public function testCreateAndUpdate()
    {
        $this->loginSimpleUser();

        $products = ProductQuery::getActiveProducts();

        $reportRows = [];

        foreach ($products as $product) {
            $reportRows[$product->getId()] = [
                'amount' => 13,
                'productId' => $product->getId(),
            ];
        }

        $params = [
            'Report' => [
                'cash' => '12.34',
                'reportRows' => $reportRows
            ]
        ];

        static::$client->request('POST', '/api/reports/', $params);

        $response = static::$client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());

        $result = json_decode($response->getContent());

        $reportId = $result->data->reportId;

        $report = ReportQuery::create()->findPk($reportId);

        $this->assertNotEmpty($report);

        $this->assertEquals(12.34, $report->getCash());

        foreach ($report->getReportRows() as $reportRow) {
            $this->assertEquals(13, $reportRow->getAmount());
        }

        // Update last products amount
        $reportRows[$product->getId()]['amount'] = 11;

        // Test if shuffling report rows will affect the outcome
        shuffle($reportRows);

        $params = [
            'Report' => [
                'cash' => '13.34',
                'reportRows' => $reportRows
            ]
        ];

        static::$client->request('PATCH', '/api/reports/'.$reportId.'/', $params);

        $response = static::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        // Reload report
        $report->reload(true);

        $this->assertEquals(13.34, $report->getCash());

        foreach ($report->getReportRows() as $reportRow) {
            if ($reportRow->getProductId() == $product->getId()) {
                $this->assertEquals(11, $reportRow->getAmount());
            } else {
                $this->assertEquals(13, $reportRow->getAmount());
            }
        }
    }

    /**
     * @dataProvider providerCreateUpdateReport
     * @param $source
     * @param $target
     */
    public function testCreateUpdateReport($source, $target)
    {
        // A le Coq Premium
        $product = ProductQuery::create()->findOneByProductCode('12345678');
        $convent = ConventQuery::create()->findOneByName('Tallinn');
        $product->setConventId($convent->getId());
        $productInfo = $product->getProductInfo();

        $addedAmount = 9;

        $expectedWarehouseAmount = $productInfo->getWarehouseAmount();
        $expectedStorageAmount = $productInfo->getStorageAmount();

        if ($source === Product::INVENTORY_TYPE_WAREHOUSE) {
            $expectedWarehouseAmount -= $addedAmount;
        }
        if ($source === Product::INVENTORY_TYPE_STORAGE) {
            $expectedStorageAmount -= $addedAmount;
        }

        if ($target === Product::INVENTORY_TYPE_STORAGE) {
            $expectedStorageAmount += $addedAmount;
        }
        if ($target === Product::INVENTORY_TYPE_WAREHOUSE) {
            $expectedWarehouseAmount += $addedAmount;
        }

        $this->loginSuperAdmin();

        $params = [
            'conventId' => $convent->getId(),
            'type' => Report::TYPE_UPDATE,
            'inventorySource' => $source,
            'inventoryTarget' => $target,
            'Report' => [
                'reportRows' => [
                    ['amount' => $addedAmount, 'productId' => $product->getId()]
                ],
            ]
        ];

        static::$client->request('POST', '/api/reports/', $params);

        $response = static::$client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $productInfo->reload();

        $this->assertEquals($expectedStorageAmount, $productInfo->getStorageAmount());
        $this->assertEquals($expectedWarehouseAmount, $productInfo->getWarehouseAmount());
    }

    public function providerCreateUpdateReport()
    {
        return [
            'warehouse_to_storage' => [Product::INVENTORY_TYPE_WAREHOUSE, Product::INVENTORY_TYPE_STORAGE],
            'storage_to_warehouse' => [Product::INVENTORY_TYPE_STORAGE, Product::INVENTORY_TYPE_WAREHOUSE],
            'to_warehouse' => [null, Product::INVENTORY_TYPE_WAREHOUSE],
            'to_storage' => [null, Product::INVENTORY_TYPE_STORAGE],
            'from_warehouse' => [Product::INVENTORY_TYPE_WAREHOUSE, null],
            'from_storage' => [Product::INVENTORY_TYPE_STORAGE, null],
        ];
    }
}
