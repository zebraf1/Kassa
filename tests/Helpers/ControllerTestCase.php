<?php

namespace Tests\Helpers;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerTestCase extends WebTestCase
{
    protected static ?KernelBrowser $client;

    public function setUp(): void
    {
        self::$client = static::createClient();
        self::$client->followRedirects();
    }

    public static function setUpBeforeClass(): void
    {
        // TODO: load fixtures
    }

    /**
     * @param string $username
     * @param string $password
     */
    protected function login(string $username, string $password): void
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

        if (empty($getResult->data->member)) {
            $this->assertNotEmpty($getResult->data->csrfToken);

            // POST - login
            $client->request(
                'POST',
                '/api/authentication/',
                [
                    'csrfToken' => $getResult->data->csrfToken,
                    'username' => $username,
                    'password' => $password,
                ]
            );

            $response = $client->getResponse();
            $postResult = json_decode($response->getContent());

            $this->assertEquals(200, $response->getStatusCode(), 'Failed: '.json_encode($postResult));
            $this->assertEquals('Autoriseerimine õnnestus', $postResult->data);
        }
    }

    protected function loginSuperAdmin(): void
    {
        $this->login('user1', 'test123');
    }

    protected function loginAdmin(): void
    {
        $this->login('user2', 'test123');
    }

    protected function loginSimpleUser(): void
    {
        $this->login('user3', 'test123');
    }
}
