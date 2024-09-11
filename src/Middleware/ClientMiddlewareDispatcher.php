<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Middleware;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;
use WebWizardry\Http\Client\ClientMiddlewareDispatcherInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientInterface;
use WebWizardry\Http\Client\ClientMiddlewareFactoryInterface;

final class ClientMiddlewareDispatcher implements ClientMiddlewareDispatcherInterface
{
    private ?ClientMiddlewareStack $stack = null;
    private array $middlewares = [];

    public function __construct(
        private readonly ClientMiddlewareFactoryInterface $factory,
    ) {}

    public function withClientMiddlewares(array $middlewares): ClientMiddlewareDispatcher
    {
        $new = clone $this;
        $new->middlewares = array_reverse($middlewares);

        unset($new->stack);
        $new->stack = null;

        return $new;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function dispatch(RequestInterface $request, ClientInterface $transport): ResponseInterface
    {
        if (null === $this->stack) {
            $this->stack = new ClientMiddlewareStack($transport, $this->buildMiddlewares());
        }
        return $this->stack->sendRequest($request);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function buildMiddlewares(): array
    {
        $middlewares = [];
        foreach ($this->middlewares as $middleware) {
            $middlewares[] = $this->factory->createMiddleware($middleware);
        }
        return $middlewares;
    }
}