<?php

namespace Rotalia\APIBundle\Tests\Controller;

use Rotalia\APIBundle\Classes\XClassifier;

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

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testGetAdmin()
    {

        $this->loginAdmin();

        static::$client->request('GET', '/api/economyReport/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        //print_r(  $result->data);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotNull($result->data);
        $this->assertNotNull($result->data->cash);
        $this->assertNotNull($result->data->LIMITED);
        $this->assertNotNull($result->data->UNLIMITED);
    }

}
