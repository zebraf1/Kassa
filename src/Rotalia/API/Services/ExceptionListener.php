<?php

namespace Rotalia\API\Services;

use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Class ExceptionListener
 * Catches exceptions and returns JSendResponse instead of default error page
 *
 * @package Rotalia\APIBundle\Controller
 */
#[AsEventListener(event: ExceptionEvent::class, method: 'onKernelException', priority: 20)]
class ExceptionListener
{
    private bool $isDebug;

    public function __construct(
        #[Autowire('%kernel.debug%')]
        bool $isDebug
    )
    {
        $this->isDebug = $isDebug;
    }

    /**
     * Catch any kernel exceptions and return a JSendResponse
     *
     * @param ExceptionEvent $event
     * @throws \Throwable
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $message = $throwable->getMessage();

        if ($throwable instanceof HttpExceptionInterface) {
            $statusCode = $throwable->getStatusCode();
        } else {
            $message = 'Unhandled exception: '.$throwable->getMessage();
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $data = $this->isDebug ? [
            'trace' => $throwable->getTraceAsString(),
        ] : null;
        $response = JSendResponse::createError($message, $statusCode, $data);

        // Send the modified response object to the event
        $event->setResponse($response);
    }
}
