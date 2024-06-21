<?php

namespace Rotalia\API\Controller;

use App\Entity\User;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class DefaultController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    protected function json(mixed $data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        if ($status < 400) {
            return self::getJSendResponse($data, $headers, $status, $this->getParameter('kernel.debug'));
        }

        return parent::json($data, $status, $headers, $context);
    }

    /**
     * @param mixed $data
     * @param array $headers
     * @param int $status
     * @param bool $isDebug
     * @return JsonResponse
     * @throws Throwable
     */
    public static function getJSendResponse(mixed $data, array $headers, int $status, bool $isDebug = false): JsonResponse
    {
        try {
            return JSendResponse::createSuccess($data, $headers, $status);
        } catch (Throwable $e) {
            // Should be a bug in the system, ie JSON encode error
            return JSendResponse::createError('Süsteemi viga', 500, [
                'error' => $e->getMessage(),
                'trace' => $isDebug ? $e->getTraceAsString() : false,
            ], null, $headers);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get(string $service)
    {
        return $this->container->get($service);
    }

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
}
