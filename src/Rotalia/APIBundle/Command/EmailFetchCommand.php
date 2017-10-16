<?php

namespace Rotalia\APIBundle\Command;


use Rotalia\APIBundle\Components\EmailFetchClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\ContextErrorException;

class EmailFetchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:email-fetch')
            ->setDescription('Checks for new emails and adds member credit')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var EmailFetchClient $fetcher */
            $fetcher = $this->getContainer()->get('rotalia.mail_fetcher');
            $output->writeln('Mailbox has '.$fetcher->numMessages().' messages');
            //TODO: retrieve and parse mails
        } catch (ContextErrorException $e) {
            $output->writeln('Error connecting to mailbox: '.$e->getMessage());
        }
    }
}
