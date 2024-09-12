<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Pool;

use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use WebWizardry\Http\Client\Curl\CurlTransport;
use WebWizardry\Http\Client\HttpClient;
use WebWizardry\Http\Client\Middleware\MiddlewareDispatcher;

final class ClientPoolFactory
{
    /**
     * @var ClientPool[]
     */
    private array $instances = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly array $servers,
        private readonly array $middlewares
    ) {}

    public function get(string $name): ClientPool
    {
        if (!isset($this->instances[$name])) {
            $this->instances[$name] = $this->instantiate($name);
        }
        return $this->instances[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->servers);
    }

    private function instantiate(string $name): ClientPool
    {
        if (!isset($this->servers[$name])) {
            throw new InvalidArgumentException(sprintf('G3 API Server "%s" config not found.', $name));
        }


        $server = $this->servers[$name];
        $connections = [];

        foreach ($server as $connection => $baseUrl) {
            $connections[$connection] = new HttpClient(
                $baseUrl,
                $this->container->get(MiddlewareDispatcher::class)->withMiddlewares($this->middlewares[$connection] ?? []),
                $this->container->get(CurlTransport::class)
            );
        }

        return new ClientPool($connections);
    }
}