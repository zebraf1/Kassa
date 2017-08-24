<?php

namespace Rotalia\APIBundle\Tests\Controller;
use Rotalia\UserBundle\Model\MemberQuery;

/**
 * Class TransfersControllerTest
 * @package Rotalia\APIBundle\Tests\Controller
 */
class TransfersControllerTest extends WebTestCase
{
    /**
     * Test list without login
     */
    public function testListUnauthorised()
    {
        static::$client->request('GET', '/api/transfers/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied.', $result->message);
    }

    /**
     * Test list without filters for admin
     */
    public function testListAdmin()
    {
        $this->loginAdmin();

        static::$client->request('GET', '/api/transfers/');
        $response = static::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test list without filters for super admin
     */
    public function testListSuper()
    {
        $this->loginSuperAdmin();

        static::$client->request('GET', '/api/transfers/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->transfers);
        $this->assertCount(3, $result->data->transfers);
    }

    /**
     * Test list with convent filter as regular user
     */
    public function testListFilterConvent()
    {
        $this->loginSimpleUser();


        static::$client->request('GET', '/api/transfers/', ['conventId' => '7']);
        $response = static::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test listing with limits
     */

    /**
     * Test list without filters for super admin
     */
    public function testListLimit()
    {
        $this->loginSuperAdmin();

        static::$client->request('GET', '/api/transfers/', ['limit' => 2]);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($result->data->transfers);
        $this->assertCount(2, $result->data->transfers);
    }

    /**
     * Test adding a transfer as an admin
     */
    public function testPostCreate()
    {
        $this->loginAdmin();

        $member = MemberQuery::create()->findOne();

        static::$client->request('POST', '/api/transfers/', [
            'TransferType' => [
                'memberId' => $member->getId(),
                'sum' => 10.5,
                'comment' => 'Test ülekanne'
            ]
        ]);

        $response = static::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $result = json_decode($response->getContent());
        $this->assertNotEmpty($result->data->transfer);
        $transfer = $result->data->transfer;

        $this->assertEquals(10.5, $transfer->sum);
        $this->assertEquals('Test ülekanne', $transfer->comment);
    }

    /**
     * Test adding a transfer as an admin
     */
    public function testPostCreateWrongConvent()
    {
        $this->loginAdmin();

        $member = MemberQuery::create()->findOne();

        static::$client->request('POST', '/api/transfers/', [
            'conventId' => 0,
            'TransferType' => [
                'memberId' => $member->getId(),
                'sum' => 10.5,
                'comment' => 'Test ülekanne'
            ]
        ]);

        $response = static::$client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

}