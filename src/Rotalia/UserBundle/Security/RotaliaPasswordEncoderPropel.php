<?php

namespace Rotalia\UserBundle\Security;

use Propel;

/**
 * Class RotaliaPasswordEncoderPropel
 * Allows password encoding and migration for Rotalia password using Propel ORM
 * @package Rotalia\UserBundle\Security
 */
class RotaliaPasswordEncoderPropel extends RotaliaPasswordEncoder
{
    public function __construct(string $plugin)
    {
        parent::__construct($plugin, Propel::getConnection());
    }
}
