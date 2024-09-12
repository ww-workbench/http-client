<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Middleware;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WebWizardry\Http\Client\Contract\ClientMiddlewareInterface;

final class MiddlewareStack
{
    private ?ClientInterface $stack = null;

    public function __construct(
        private readonly ClientInterface $transport,
        private readonly array $middlewares = []
    ) {}

    /**
     * @throws ClientExceptionInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if (null === $this->stack) {
            $this->build();
        }
        return $this->stack->sendRequest($request);
    }

    private function build(): void
    {
        $transport = $this->transport;
        foreach ($this->middlewares as $middleware) {
            $transport = $this->wrapTransport($middleware, $transport);
        }
        $this->stack = $transport;
    }

    private function wrapTransport(ClientMiddlewareInterface $middleware, ClientInterface $transport): ClientInterface
    {
        return new readonly class ($middleware, $transport) implements ClientInterface
        {
            public function __construct(
                private ClientMiddlewareInterface $middleware,
                private ClientInterface           $transport
            ) {}

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                return $this->middleware->process($request, $this->transport);
            }
        };
    }
}