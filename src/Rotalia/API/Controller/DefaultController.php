<?php

namespace Rotalia\API\Controller;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function get(string $service): mixed
    {
        return $this->container->get($service);
    }

    protected function json(mixed $data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        if ($status < 400) {
            try {
                return JSendResponse::createSuccess($data, $headers, $status);
            } catch (Exception $e) {
                // Should be a bug in the system, ie JSON encode error
                return parent::json([
                    'status' => JSendResponse::JSEND_STATUS_ERROR,
                    'message' => 'Süsteemi viga, raporteeri',
                    'data' => [
                        'error' => $e->getMessage(),
//                        'trace' => $e->getTraceAsString(),
                    ],
                ], 500);
            }
        }

        return parent::json($data, $status, $headers, $context);
    }
}
