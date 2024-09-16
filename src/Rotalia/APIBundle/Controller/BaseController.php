<?php

namespace Rotalia\APIBundle\Controller;

use Psr\Log\LoggerInterface;
use Rotalia\UserBundle\Model\User;
use Rotalia\APIBundle\Model\PointOfSaleQuery;
use Rotalia\UserBundle\Model\Member;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BaseController extends AbstractController
{
    /**
     * @throws AccessDeniedHttpException
     */
    protected function requireSuperAdmin(): void
    {
        if (!$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * @throws AccessDeniedHttpException
     */
    protected function requireAdmin(): void
    {
        if (!$this->isGranted(User::ROLE_ADMIN)) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * @throws AccessDeniedHttpException
     */
    protected function requireUser(): void
    {
        if (!$this->isGranted(User::ROLE_USER)) {
            throw new AccessDeniedHttpException();
        }
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

    /**
     * @param Request $request
     * @return null|\Rotalia\APIBundle\Model\PointOfSale
     */
    protected function getPos(Request $request)
    {
        $hash = $request->cookies->get('pos_hash');
        $pos = null;
        if ($hash) {
            $pos = PointOfSaleQuery::create()->findOneByHash($hash);
        }

        return $pos;
    }
}
