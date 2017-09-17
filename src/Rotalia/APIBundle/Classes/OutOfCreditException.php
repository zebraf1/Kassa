<?php

namespace Rotalia\APIBundle\Classes;

use Throwable;

class OutOfCreditException extends \Exception
{
    public function __construct($newCredit, $creditLimit, Throwable $previous = null)
    {
        parent::__construct('Krediit on otsas, su limiit on '.$creditLimit.'. Sinu uus krediit oleks olnud '.$newCredit, 0, $previous);
    }
}
