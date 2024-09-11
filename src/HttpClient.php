<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WebWizardry\Http\Client\Connection\ConnectionInterface;
use WebWizardry\Http\Client\Connection\ConnectionPoolInterface;

final class HttpClient implements HttpClientInterface
{
    public function __construct(
        private ConnectionPoolInterface $connectionPool,
        private readonly ClientMiddlewareDispatcherInterface $dispatcher,
        private readonly ClientInterface $transport
    ){}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->dispatcher->dispatch($request, $this->transport);
    }

    public function getBaseUri(string $alternative = null): string
    {
        return $this->connectionPool->getBaseUri($alternative);
    }

    public function useConnection(string $connectionName): HttpClient
    {
        $this->connectionPool->useConnection($connectionName);
        return $this;
    }

    public function withConnections(array $connections): ConnectionPoolInterface
    {
        $pool = $this->connectionPool;

        $new = clone $this;

        unset($new->connectionPool);
        $new->connectionPool = $pool->withConnections($connections);

        return $new;
    }

    public function getCurrentConnection(): ConnectionInterface
    {
        return $this->connectionPool->getCurrentConnection();
    }
}