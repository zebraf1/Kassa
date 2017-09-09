<?php

namespace Rotalia\InventoryBundle\Tests\Controller;


use Rotalia\APIBundle\Tests\Controller\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomeSuccessful()
    {
        $this->loginSimpleUser();
        $crawler = self::$client->request('GET', '/vana/');

        $response = static::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Failed asserting that homepage returns 200');
        $this->assertEquals('Kassa aruanne', $crawler->filter('h1')->first()->text());
    }

    public function testHomeUnauthorizedRedirect()
    {
        self::$client->request('GET', '/vana/');

        $response = static::$client->getResponse();
        $this->assertTrue($response->isRedirection(), 'Failed asserting that homepage was redirected');
    }
}
