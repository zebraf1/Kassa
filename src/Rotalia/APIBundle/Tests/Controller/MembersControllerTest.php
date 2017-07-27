<?php

namespace Rotalia\APIBundle\Tests\Controller;


class MembersControllerTest extends WebTestCase
{
    public function testList()
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/members/', ['name' => 'oluli']);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode(), 'Error: '.$response->getContent());
        $this->assertTrue(!empty($result->data));
        $this->assertTrue(!empty($result->data->members), 'Failed asserting that members were returned');
        $this->assertInternalType('array', $result->data->members);
        $this->assertCount(2, $result->data->members);

        $expectedMembers = ['Keegi Oluline', 'Super Oluline'];
        $memberNames = array_map(function ($a) { return $a->name;}, $result->data->members);
        $this->assertEquals($expectedMembers, $memberNames);
    }

    public function testListPagination()
    {
        $this->loginSimpleUser();

        static::$client->request('GET', '/api/members/', [
            'name' => 'oluli',
            'limit' => 1,
            'offset' => 1,
        ]);
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode(), 'Error: '.$response->getContent());
        $this->assertTrue(!empty($result->data));
        $this->assertTrue(!empty($result->data->members), 'Failed asserting that members were returned');
        $this->assertInternalType('array', $result->data->members);
        $this->assertCount(1, $result->data->members);

        $expectedMembers = ['Super Oluline'];
        $memberNames = array_map(function ($a) { return $a->name;}, $result->data->members);
        $this->assertEquals($expectedMembers, $memberNames);
    }
}
