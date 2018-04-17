<?php

namespace Rotalia\APIBundle\Tests\Controller;

use Rotalia\InventoryBundle\Model\Product;
use Rotalia\InventoryBundle\Model\ProductQuery;
use Rotalia\InventoryBundle\Model\Report;
use Rotalia\InventoryBundle\Model\ReportQuery;
use Rotalia\UserBundle\Model\ConventQuery;

class EconomyReportControllerTest extends WebTestCase
{
    public function testGetUnauthorised()
    {
        static::$client->request('GET', '/api/economyReport/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied.', $result->message);
    }

    public function testGetSimpleUser()
    {

        $this->loginSimpleUser();

        static::$client->request('GET', '/api/economyReport/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(403, $response->getStatusCode());
    }

}
