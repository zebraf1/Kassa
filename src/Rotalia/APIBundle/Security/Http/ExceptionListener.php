<?php

namespace Rotalia\APIBundle\Security\Http;

use Rotalia\APIBundle\Controller\DefaultController;
use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ExceptionListener
 * Catches exceptions and returns JSendResponse instead of default error page
 *
 * @package Rotalia\APIBundle\Controller
 */
class ExceptionListener
{
    /**
     * Catch any kernel exceptions and return a JSendResponse
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();
        $controllerNamespace = $request->attributes->get('_controller');
        $controllerClass = substr($controllerNamespace, 0, strpos($controllerNamespace, '::'));

        // Handle all APIBundle controller request errors
        if ($controllerClass && (new $controllerClass) instanceof DefaultController) {
            $message = $exception->getMessage();

            if ($exception instanceof HttpExceptionInterface) {
                $statusCode = $exception->getStatusCode();
            } elseif ($exception instanceof AccessDeniedException) {
                $statusCode = Response::HTTP_FORBIDDEN;
            } else {
                $message = 'Unhandled exception: '.$exception->getMessage();
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }

            $response = JSendResponse::createError($message, $statusCode);

            // Send the modified response object to the event
            $event->setResponse($response);
        }
    }
}