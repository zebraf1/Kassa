<?php

namespace Rotalia\APIBundle\Tests\Controller;

use Rotalia\APIBundle\Model\CreditNettingQuery;
use Rotalia\UserBundle\Model\ConventQuery;

/**
 * Class CreditNettingsControllerTest
 * @package Rotalia\APIBundle\Tests\Controller
 */
class CreditNettingsControllerTest extends WebTestCase
{
    /**
     * Test list without login
     */
    public function testListUnauthorised()
    {
        static::$client->request('GET', '/api/creditNettings/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied.', $result->message);
    }

    /**
     * Test list as regular user
     */
    public function testList()
    {
        // test controller
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/creditNettings/');
        $response = static::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());

    }

    /***
     * test list as an admin
     */
    public function testListAdmin()
    {

        // test controller
        $this->loginAdmin();

        static::$client->request('GET', '/api/creditNettings/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(1, $result->data->creditNettings);

        foreach ($result->data->creditNettings as $creditNetting) {
            $sum = 0;
            foreach ($creditNetting->creditNettingRows as $creditNettingRow) {
                $sum += $creditNettingRow->sum;
            }
            $this->assertEquals(0, $sum);
        }

    }

    /**
     * Test patch as regular user
     */
    public function testPatch()
    {
        // test controller
        $this->loginSimpleUser();

        $creditNetting = CreditNettingQuery::create()->findOne();
        $conventId = ConventQuery::create()
            ->filterByName('Tallinn')
            ->findOne()
            ->getId()
        ;

        static::$client->request('PATCH', '/api/creditNettings/'.$creditNetting->getId().'/', [
            'conventId' => $conventId,
            'CreditNettingRowType' => ['nettingDone' => 1]
        ]);
        $response = static::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());

    }

    /**
     * Test patch as admin
     */
    public function testPatchAdmin()
    {
        // test controller
        $this->loginAdmin();

        $creditNetting = CreditNettingQuery::create()->findOne();
        $conventId = ConventQuery::create()
            ->filterByName('Tallinn')
            ->findOne()
            ->getId()
        ;

        static::$client->request('PATCH', '/api/creditNettings/'.$creditNetting->getId().'/', [
            'conventId' => $conventId,
            'CreditNettingRowType' => ['nettingDone' => 1]
        ]);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());

        foreach ($result->data->creditNetting->creditNettingRows as $creditNettingRow) {
            $this->assertEquals(
                $creditNettingRow->conventId == $conventId ? 1 : 0,
                $creditNettingRow->nettingDone
            );
        }

    }

}