<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Connection;

use InvalidArgumentException;

final class ConnectionPool implements ConnectionPoolInterface
{
    private array $connections = [];
    private ?string $currentConnection = null;

    private function setConnection(string $name, string $baseUri, array $alternative = []): void
    {
        $this->connections[$name] = new Connection($baseUri, $alternative);
        if (null === $this->currentConnection) {
            $this->currentConnection = $name;
        }
    }

    public function withConnections(array $connections): ConnectionPoolInterface
    {
        $new = clone $this;

        $new->connections = [];
        foreach ($connections as $name => $connection) {
            if (isset($connection['baseUri'])) {
                $new->setConnection($name, $connection['baseUri'], $connection['alternative'] ?? []);
            }
        }

        return $new;
    }

    public function getBaseUri(string $alternative = null): string
    {
        return $this->getCurrentConnection()->getBaseUri($alternative);
    }

    public function useConnection(string $connectionName): ConnectionPool
    {
        if (array_key_exists($connectionName, $this->connections)) {
            $this->currentConnection = $connectionName;
            return $this;
        }
        throw new InvalidArgumentException("Connection $connectionName does not exist.");
    }

    public function getCurrentConnection(): ConnectionInterface
    {
        if (array_key_exists($this->currentConnection, $this->connections)) {
            return $this->connections[$this->currentConnection];
        }
        throw new InvalidArgumentException("Connection {$this->currentConnection} does not exist.");
    }
}