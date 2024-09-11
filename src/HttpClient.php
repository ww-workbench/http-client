<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class HttpClient implements HttpClientInterface
{
    public function __construct(
        private ClientMiddlewareDispatcherInterface $dispatcher,
        private ClientInterface                     $transport
    ){}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->dispatcher->dispatch($request, $this->transport);
    }

    public function withTemporaryMiddlewares(array $middlewares): HttpClientInterface
    {
        return new self(
            dispatcher: $this->dispatcher->withClientMiddlewares($middlewares),
            transport: $this
        );
    }
}