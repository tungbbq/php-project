<?php

namespace App\EventListener;

use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();
        $message = sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );

        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent($message);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        }elseif ($exception instanceof ConnectionException){
            $content =  ['status' => 'failure', 'message' => 'Verbindungsfehler mit der Datenbank, Sorry!'];
            $response->setContent(json_encode($content));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        } elseif ($exception instanceof \TypeError){
            $content =  ['status' => 'failure', 'message' => $exception->getMessage()];
            $response->setContent(json_encode($content));
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

        }
        else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}