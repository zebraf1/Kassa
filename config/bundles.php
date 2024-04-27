<?php

use Rotalia\APIBundle\RotaliaAPIBundle;
use Rotalia\FrontendBundle\RotaliaFrontendBundle;

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    RotaliaAPIBundle::class => ['all' => true],
    RotaliaFrontendBundle::class => ['all' => true],
];
