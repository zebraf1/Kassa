<?php

namespace Rotalia\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class RotaliaUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
