<?php

namespace Rotalia\APIBundle\Controller;


use Rotalia\InventoryBundle\Controller\BaseController;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

/**
 * Base controller for API bundle controllers
 *
 * Class DefaultController
 * @package Rotalia\APIBundle\Controller
 */
class DefaultController extends BaseController
{
    /**
     * Note: CsrfProviderInterface is deprecated. Upgrade FosUserBundle to fix this and use CsrfTokenManagerInterface
     * @return CsrfProviderInterface
     */
    protected function getCSRFProvider()
    {
        return $this->get('form.csrf_provider'); // todo: use security.csrf.token_manager since symfony 3.0
    }
}
