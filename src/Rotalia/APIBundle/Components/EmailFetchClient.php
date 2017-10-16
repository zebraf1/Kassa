<?php

namespace Rotalia\APIBundle\Components;

use Fetch\Server;

/**
 * Class EmailFetchClient
 * @package Rotalia\APIBundle\Components
 */
class EmailFetchClient extends Server
{
    /**
     * EmailFetchClient constructor.
     *
     * @param string      $serverPath
     * @param int|null    $port
     * @param null|string $service
     * @param             $username
     * @param             $password
     * @param string      $mailbox
     */
    public function __construct($serverPath, $port, $service, $username, $password, $mailbox = '')
    {
        parent::__construct($serverPath, $port, $service);

        $this->setAuthentication($username, $password);
        $this->setFlag('novalidate-cert');
        $this->setMailBox($mailbox);
    }

    /**
     * Returns the server specification, without adding any mailbox.
     *
     * @return string
     */
    protected function getServerSpecification()
    {
        $mailboxPath = '{' . $this->serverPath;

        if (isset($this->port)) {
            $mailboxPath .= ':' . $this->port;
        }

        // CHANGED: Gmail requires imap in server path?
        if ($this->service != 'imap' || $this->serverPath == 'imap.gmail.com') {
            $mailboxPath .= '/' . $this->service;
        }

        foreach ($this->flags as $flag) {
            $mailboxPath .= '/' . $flag;
        }

        $mailboxPath .= '}';

        return $mailboxPath;
    }
}
