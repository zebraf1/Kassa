<?php

namespace Rotalia\APIBundle\Tests\Command;

use Rotalia\APIBundle\Command\CreditNettingCommand;
use Rotalia\APIBundle\Tests\Controller\WebTestCase;
use Rotalia\InventoryBundle\Model\CreditNetting;
use Rotalia\InventoryBundle\Model\CreditNettingQuery;
use Symfony\Component\Console\Tester\CommandTester;


class CreditNettingCommandTestCase extends WebTestCase
{

    public function testExecute()
    {
        $application = self::getApplication();

        $application->add(new CreditNettingCommand());

        $command = $application->find('app:credit-netting');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName()
        ));

        $creditNettings = CreditNettingQuery::create()->find();

        $this->assertCount(2, $creditNettings);

        /** @var CreditNetting $creditNetting */
        foreach ($creditNettings as $creditNetting) {
            $this->assertCount(2, $creditNetting->getCreditNettingRows());

            $sum = 0;
            foreach ($creditNetting->getCreditNettingRows() as $creditNettingRow) {
                $sum += doubleval($creditNettingRow->getSum());
            }

            $this->assertEquals(0, $sum);
        }

    }
}
