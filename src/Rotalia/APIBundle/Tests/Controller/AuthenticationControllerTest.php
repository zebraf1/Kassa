<?php

namespace Rotalia\APIBundle\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AuthenticationControllerTest
 * @package Rotalia\APIBundle\Tests\Controller
 */
class AuthenticationControllerTest extends WebTestCase
{
    public function testGetAction()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/authentication/'
        );

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $result = json_decode($response->getContent());

        $this->assertEquals('success', $result->status);
        $this->assertEquals(null, $result->data->member);
        $this->assertEquals(null, $result->data->pointOfSale);
        $this->assertNotEmpty($result->data->csrfToken);
    }
}
