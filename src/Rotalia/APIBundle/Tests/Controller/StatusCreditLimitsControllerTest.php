<?php

namespace Rotalia\APIBundle\Tests\Controller;


use Rotalia\UserBundle\Model\StatusCreditLimitQuery;
use Rotalia\UserBundle\Model\StatusQuery;

class StatusCreditLimitsControllerTest extends WebTestCase
{
    public function testListUnauthorized()
    {
        self::$client->request('GET', '/api/statusCreditLimits/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied.', $result->message);
    }

    public function testList()
    {
        $this->loginSimpleUser();

        self::$client->request('GET', '/api/statusCreditLimits/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->creditLimits);
        $expectedCount = StatusQuery::create()->count();
        $this->assertCount($expectedCount, $result->data->creditLimits, 'Failed asserting that all credit limits were returned');
    }

    public function testPatchAccessDenied()
    {
        $this->loginAdmin();
        self::$client->request('PATCH', '/api/statusCreditLimits/22/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied.', $result->message);
    }

    public function testPatchNotFound()
    {
        $this->loginSuperAdmin();
        $status = StatusQuery::create()->orderById(\Criteria::DESC)->limit(1)->findOne();

        self::$client->request('PATCH', '/api/statusCreditLimits/'.($status->getId() + 1).'/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Staatust ei leitud', $result->message);
    }

    public function testPatch()
    {
        $this->loginSuperAdmin();
        $status = StatusQuery::create()->findOne();

        self::$client->request('PATCH', '/api/statusCreditLimits/'.$status->getId().'/', [
            'creditLimit' => 10,
        ]);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(-10, $result->data->creditLimit);

        // Validate database
        $creditLimit = StatusCreditLimitQuery::create()->findOneByStatusId($status->getId());
        $this->assertNotEmpty($creditLimit);
        $this->assertEquals(-10, $creditLimit->getCreditLimit());
    }
}
