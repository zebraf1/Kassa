<?php

namespace Rotalia\APIBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\Console\Input\StringInput;

class WebTestCase extends BaseWebTestCase
{
    private static ?Application $application;
    protected static ?KernelBrowser $client;

    public function setUp(): void
    {
        self::$client = static::createClient();
        self::$client->followRedirects();
    }

    public static function setUpBeforeClass(): void
    {
//        \Propel::disableInstancePooling();
        self::loadFixtures();
    }

    protected static function getApplication(): Application
    {
        if (self::$application !== null) {
            return self::$application;
        }

        $client = static::createClient();
        $application = new Application($client->getKernel());
        $application->setAutoExit(false);
        self::$application = $application;

        return $application;
    }

    protected static function runCommand($command)
    {
        $command = sprintf('%s', $command);

        return self::getApplication()->run(new StringInput($command));
    }


    protected static function loadFixtures()
    {
//        self::runCommand('propel:fixtures:load @RotaliaAPIBundle --env=test --quiet');
    }

    /**
     * @param $username
     * @param $password
     */
    protected function login($username, $password)
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

    protected function loginSuperAdmin()
    {
        $this->login('user1', 'test123');
    }

    protected function loginAdmin()
    {
        $this->login('user2', 'test123');
    }

    protected function loginSimpleUser()
    {
//        $this->login('user3', 'test123');
    }
}
