<?php

namespace Rotalia\APIBundle;

use Rotalia\APIBundle\DependencyInjection\RotaliaAPIExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RotaliaAPIBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new RotaliaAPIExtension();
    }
}
