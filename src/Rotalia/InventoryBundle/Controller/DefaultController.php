<?php

namespace Rotalia\InventoryBundle\Controller;

use Rotalia\UserBundle\Model\Member;
use Rotalia\UserBundle\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class DefaultController
 * @package Rotalia\InventoryBundle\Controller
 * @method User getUser
 */
class DefaultController extends Controller
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

    /**
     * Check that current user has given role
     *
     * @param $role
     * @return bool
     */
    protected function isGranted($role)
    {
        /** @var SecurityContextInterface $securityContext */
        $securityContext = $this->get('security.context');
        return $securityContext->isGranted($role);
    }

    /**
     * @return null|Member
     */
    public function getMember()
    {
        if ($user = $this->getUser()) {
            return $user->getMember();
        }

        return null;
    }
}
