<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientInterface;
use WebWizardry\Http\Client\Middleware\ClientMiddlewareDispatcher;

interface ClientMiddlewareDispatcherInterface
{
    public function withClientMiddlewares(array $middlewares): ClientMiddlewareDispatcher;
    public function dispatch(RequestInterface $request, ClientInterface $transport): ResponseInterface;
}