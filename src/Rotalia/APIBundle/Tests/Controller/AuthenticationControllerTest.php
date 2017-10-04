<?php

namespace Rotalia\APIBundle\Tests\Controller;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class AuthenticationControllerTest
 * @package Rotalia\APIBundle\Tests\Controller
 */
class AuthenticationControllerTest extends WebTestCase
{
    /**
     * Check that user is not logged in and fetch login token
     * Attempt login, check that it is successful
     * Get login info, check that user name is correct
     * Logout
     * Check that user is logged out
     *
     * @dataProvider providerSuccessfulLoginAndLogout
     * @param bool $rememberMe
     */
    public function testSuccessfulLoginAndLogout($rememberMe = false)
    {
        $client = static::$client;

        // GET - fetch CSRF Token
        $client->request(
            'GET',
            '/api/authentication/'
        );

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $getResult = json_decode($response->getContent());

        $this->assertEquals('success', $getResult->status);
        $this->assertEquals(null, $getResult->data->member);
        $this->assertEquals(null, $getResult->data->pointOfSaleId);
        $this->assertNotEmpty($getResult->data->csrfToken);

        // POST - login
        $client->request(
            'POST',
            '/api/authentication/',
            [
                'csrfToken' => $getResult->data->csrfToken,
                'username' => 'user1', // from fixtures
                'password' => 'test123', // from fixtures
                'rememberMe' => $rememberMe,
            ]
        );

        $response = $client->getResponse();
        $postResult = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode(), 'Failed: '.json_encode($postResult));
        $this->assertEquals('Autoriseerimine õnnestus', $postResult->data);

        $postResult = json_decode($response->getContent());
        $this->assertEquals('success', $postResult->status);

        /** @var Cookie[] $cookies */
        $cookies = $response->headers->getCookies();
        /** @var Cookie $sessionCookie */
        $sessionCookie = $cookies[$rememberMe ? 1 : 0];
        $this->assertEquals('MOCKSESSID', $sessionCookie->getName());
        $sessionId = $sessionCookie->getValue();

        // Check if remember me cookie is set
        if ($rememberMe) {
            $rememberMeCookie = $cookies[0];
            $this->assertEquals('REMEMBERME', $rememberMeCookie->getName());
        }

        $clientCookie = $client->getCookieJar()->get('MOCKSESSID');
        $this->assertEquals($sessionId, $clientCookie->getValue());

        // GET - check login status
        $client->request(
            'GET',
            '/api/authentication/'
        );

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $getResult = json_decode($response->getContent());

        $this->assertEquals('success', $getResult->status);
        $this->assertNotEmpty($getResult->data->member);
        $this->assertEquals('Super Oluline', $getResult->data->member->name);
        $this->assertEquals(null, $getResult->data->pointOfSaleId);
        $this->assertEmpty($getResult->data->csrfToken);

        // DELETE - logout
        $client->request(
            'DELETE',
            '/api/authentication/'
        );

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $deleteResult = json_decode($response->getContent());

        $this->assertEquals('success', $deleteResult->status);

        // GET - check that user is logged out
        $client->request(
            'GET',
            '/api/authentication/'
        );

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $getResult = json_decode($response->getContent());
        $this->assertEquals('success', $getResult->status);
        $this->assertEquals(null, $getResult->data->member);
        $this->assertEquals(null, $getResult->data->pointOfSaleId);
        $this->assertNotEmpty($getResult->data->csrfToken);
    }

    /**
     * @return array
     */
    public function providerSuccessfulLoginAndLogout()
    {
        return [
            [true], // rememberMe - check that logout works properly
            [false], // don't remember ME
        ];
    }

    // Test authentication failed 400 403
    public function testAuthenticationWithoutToken()
    {
        $client = static::$client;

        // POST - login
        $client->request(
            'POST',
            '/api/authentication/',
            [
                'username' => 'user1', // from fixtures
                'password' => 'test123', // from fixtures
            ]
        );

        $response = $client->getResponse();
        $postResult = json_decode($response->getContent());

        $this->assertEquals(400, $response->getStatusCode(), 'Failed: '.json_encode($postResult));
        $this->assertEquals('Login token puudub', $postResult->message);
    }

    public function testAuthenticationWrongUsername()
    {
        $client = static::$client;

        // GET - fetch CSRF Token
        $client->request(
            'GET',
            '/api/authentication/'
        );

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $getResult = json_decode($response->getContent());

        // POST - login
        $client->request(
            'POST',
            '/api/authentication/',
            [
                'csrfToken' => $getResult->data->csrfToken,
                'username' => 'wrong',
                'password' => 'test123',
            ]
        );

        $response = $client->getResponse();
        $postResult = json_decode($response->getContent());

        $this->assertEquals(401, $response->getStatusCode(), 'Failed: '.json_encode($postResult));
        $this->assertEquals('Kasutajat ei leitud', $postResult->message);
    }

    public function testAuthenticationWrongPassword()
    {
        $client = static::$client;

        // GET - fetch CSRF Token
        $client->request(
            'GET',
            '/api/authentication/'
        );

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $getResult = json_decode($response->getContent());

        // POST - login
        $client->request(
            'POST',
            '/api/authentication/',
            [
                'csrfToken' => $getResult->data->csrfToken,
                'username' => 'user1',
                'password' => 'wrong',
            ]
        );

        $response = $client->getResponse();
        $postResult = json_decode($response->getContent());

        $this->assertEquals(401, $response->getStatusCode(), 'Failed: '.json_encode($postResult));
        $this->assertEquals('Vale kasutaja/parool', $postResult->message);
    }
}
