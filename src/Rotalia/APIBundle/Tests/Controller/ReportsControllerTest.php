<?php

namespace Rotalia\APIBundle\Tests\Controller;


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
}
