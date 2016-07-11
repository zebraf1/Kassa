<?php

namespace Rotalia\InventoryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class DefaultController
 * @package Rotalia\InventoryBundle\Controller
 */
class DefaultController extends BaseController
{
    const FLASH_OK = 'ok';
    const FLASH_ERROR = 'error';

    /**
     * @param Request $request
     * @param string|array $message
     */
    protected function setFlashOk(Request $request, $message)
    {
        foreach ((array)$message as $flash) {
            $request->getSession()->getFlashBag()->add(
                self::FLASH_OK,
                $flash
            );
        }
    }

    /**
     * @param Request $request
     * @param string|array $message
     */
    protected function setFlashError(Request $request, $message)
    {
        foreach ((array)$message as $flash) {
            $request->getSession()->getFlashBag()->add(
                self::FLASH_ERROR,
                $flash
            );
        }
    }
}
