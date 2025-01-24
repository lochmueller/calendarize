<?php

namespace HDNET\Calendarize\Event;

use HDNET\Calendarize\Controller\AbstractController;
use Psr\Http\Message\ResponseInterface;

final class DirectResponseEvent
{
    public function __construct(protected ResponseInterface $response, protected AbstractController $controller) {}

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getController(): AbstractController
    {
        return $this->controller;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
