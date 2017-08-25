<?php

namespace Rotalia\InventoryBundle\Tests\Controller;


use Rotalia\APIBundle\Tests\Controller\WebTestCase;
use Rotalia\InventoryBundle\Model\ProductQuery;

class InventoryControllerTest extends WebTestCase
{
    public function testEditView()
    {
        $product = ProductQuery::create()->findOneByName('A le Coq Premium');
        $this->loginAdmin();
        $crawler = self::$client->request('GET', '/tooted/muuda/'.$product->getId().'/');

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('A le Coq Premium', $crawler->filter('#ProductType_name')->first()->attr('value'));
    }
}
