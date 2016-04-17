<?php

namespace Rotalia\InventoryBundle\Controller;


use Rotalia\InventoryBundle\Model\PointOfSaleQuery;
use Rotalia\UserBundle\Model\User;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class HomeController extends DefaultController
{
    public function homeAction(Request $request)
    {
        // Logged in user is shown reports page
        if ($this->isGranted(User::ROLE_USER)) {
            return $this->forward('RotaliaInventoryBundle:Report:list');
        }

        $hash = $request->cookies->get('pos_hash');

        // Redirect to login page
        if (!$hash) {
            throw new AccessDeniedException();
        }

        $pos = PointOfSaleQuery::create()->findOneByHash($hash);
        // Check if this PointOfSale has been deleted

        if (!$pos) {
            // Remove cookie and redirect to login page
            $response = $this->redirect($this->generateUrl('fos_user_security_login'));
            $response->headers->clearCookie('pos_hash');
            return $response;
        }

        $response = $this->forward('RotaliaInventoryBundle:Purchase:home');
        // Refresh cookie
        $response->headers->setCookie(new Cookie('pos_hash', $hash, new \DateTime('+1 year')));

        return $response;
    }
}