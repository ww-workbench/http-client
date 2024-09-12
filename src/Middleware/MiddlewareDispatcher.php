<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Middleware;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class MiddlewareDispatcher
{
    private ?MiddlewareStack $stack = null;
    private array $middlewares = [];

    public function __construct(
        private readonly MiddlewareFactory $factory,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function dispatch(RequestInterface $request, ClientInterface $transport): ResponseInterface
    {
        if (null === $this->stack) {
            $this->stack = new MiddlewareStack($transport, $this->buildMiddlewares());
        }
        return $this->stack->sendRequest($request);
    }

    public function withMiddlewares(array $middlewares): MiddlewareDispatcher
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