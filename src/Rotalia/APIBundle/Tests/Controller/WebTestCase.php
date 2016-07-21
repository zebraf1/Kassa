<?php

namespace Rotalia\APIBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\Console\Input\StringInput;

class WebTestCase extends BaseWebTestCase
{
    private static $application;

    /**
     * @var Client
     */
    protected static $client;

    public function setUp()
    {
        // this is the part that should make things work
        $options = array(
            'environment' => 'test'
        );
        self::$client = static::createClient($options);
    }

    public static function setUpBeforeClass()
    {
        \Propel::disableInstancePooling();

//        self::runCommand('propel:build --insert-sql');
        self::runCommand('propel:fixtures:load @RotaliaAPIBundle --env=test');
    }

    protected static function getApplication()
    {
        if (null === self::$application) {
            $client = static::createClient();

            self::$application = new Application($client->getKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }

    protected static function runCommand($command)
    {
        $command = sprintf('%s', $command);

        return self::getApplication()->run(new StringInput($command));
    }
}