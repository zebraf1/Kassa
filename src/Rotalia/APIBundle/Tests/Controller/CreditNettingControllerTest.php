<?php

namespace Rotalia\APIBundle\Tests\Controller;
use Rotalia\APIBundle\Command\CreditNettingCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CreditNettingControllerTest
 * @package Rotalia\APIBundle\Tests\Controller
 */
class CreditNettingControllerTest extends WebTestCase
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

    /***
     * Test list with no nettings in the database.
     */
    public function testListEmpty()
    {
        $this->loginAdmin();

        static::$client->request('GET', '/api/creditNettings/');
        $response = static::$client->getResponse();
        $result = json_decode($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty($result->data->creditNettings);

    }

    /***
     * test list after netting is done
     */
    public function testList()
    {

        // run credit netting
        $kernel = $this->createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new CreditNettingCommand());

        $command = $application->find('app:credit-netting');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

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

}