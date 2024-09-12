<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Middleware;

use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use WebWizardry\Http\Client\Contract\ClientMiddlewareInterface;

final readonly class MiddlewareFactory
{
    public function __construct(
        private ?ContainerInterface $container = null
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createMiddleware($definition): ClientMiddlewareInterface
    {
        if ($definition instanceof ClientMiddlewareInterface) {
            return $definition;
        }

        if (!$this->container) {
            throw new LogicException('Container required to resolve definitions');
        }

        $middleware = $this->container->get($definition);
        assert($middleware instanceof ClientMiddlewareInterface);
        return $middleware;
    }
}