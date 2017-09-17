<?php

namespace Rotalia\InventoryBundle\Controller;

use Psr\Log\LoggerInterface;
use Rotalia\UserBundle\Model\User;
use Rotalia\InventoryBundle\Model\PointOfSaleQuery;
use Rotalia\UserBundle\Model\Member;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class BaseController extends Controller
{
    /**
     * @throws AccessDeniedException
     */
    protected function requireSuperAdmin()
    {
        if (!$this->isGranted(User::ROLE_SUPER_ADMIN)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @throws AccessDeniedException
     */
    protected function requireAdmin()
    {
        if (!$this->isGranted(User::ROLE_ADMIN)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @throws AccessDeniedException
     */
    protected function requireUser()
    {
        if (!$this->isGranted(User::ROLE_USER)) {
            throw new AccessDeniedException();
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
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->get('logger');

        return $logger;
    }

    /**
     * @param Request $request
     * @return null|\Rotalia\InventoryBundle\Model\PointOfSale
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
