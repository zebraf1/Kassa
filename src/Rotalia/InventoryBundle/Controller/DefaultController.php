<?php

namespace Rotalia\InventoryBundle\Controller;

use Rotalia\UserBundle\Model\Member;
use Rotalia\UserBundle\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class DefaultController
 * @package Rotalia\InventoryBundle\Controller
 * @method User getUser
 */
class DefaultController extends Controller
{
    protected function setFlash($type, $message) {
        $this->getRequest()->getSession()->getFlashBag()->add(
            $type,
            $message
        );
    }

    /**
     * Check that current user has given role
     *
     * @param $role
     * @return bool
     */
    protected function isGranted($role) {
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
