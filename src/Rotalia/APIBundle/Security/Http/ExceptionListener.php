<?php

namespace Rotalia\APIBundle\Security\Http;

use Rotalia\APIBundle\Controller\DefaultController;
use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Class ExceptionListener
 * Catches exceptions and returns JSendResponse instead of default error page
 *
 * @package Rotalia\APIBundle\Controller
 */
class ExceptionListener extends ErrorListener
{
    /**
     * Catch any kernel exceptions and return a JSendResponse
     *
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if (null === $this->controller) {
            return;
        }

        $throwable = $event->getThrowable();

        if ($exceptionHandler = set_exception_handler(var_dump(...))) {
            restore_exception_handler();
            if (\is_array($exceptionHandler) && $exceptionHandler[0] instanceof ErrorHandler) {
                $throwable = $exceptionHandler[0]->enhanceError($event->getThrowable());
            }
        }

        $request = $this->duplicateRequest($throwable, $event->getRequest());

        $controllerNamespace = $request->attributes->get('_controller');
        $controllerClass = substr($controllerNamespace, 0, strpos($controllerNamespace, '::'));

        // Handle all APIBundle controller request errors
        if ($controllerClass && (new $controllerClass) instanceof DefaultController) {
            $message = $throwable->getMessage();

            if ($throwable instanceof HttpExceptionInterface) {
                $statusCode = $throwable->getStatusCode();
            } else {
                $message = 'Unhandled exception: '.$throwable->getMessage();
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }

            $response = JSendResponse::createError($message, $statusCode);

            // Send the modified response object to the event
            $event->setResponse($response);
        }
    }
}