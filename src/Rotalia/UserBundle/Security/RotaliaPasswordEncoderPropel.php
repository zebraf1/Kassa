<?php

namespace Rotalia\UserBundle\Security;

use Propel;
use PropelException;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Class RotaliaPasswordEncoderPropel
 * Allows password encoding and migration for Rotalia password using Propel ORM
 * @package Rotalia\UserBundle\Security
 */
class RotaliaPasswordEncoderPropel extends RotaliaPasswordEncoder implements PasswordEncoderInterface
{
    /**
     * @param string $string
     * @return string
     * @throws PropelException
     */
    protected function escapeString(string $string): string
    {
        return Propel::getConnection()->quote($string);
    }

    /**
     * @param string $sql
     * @return string
     * @throws PropelException
     */
    protected function runQuery(string $sql): string
    {
        $con = Propel::getConnection();
        $stmt = $con->query($sql);
        return $stmt->fetchColumn();
    }
}
