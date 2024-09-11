<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Connection;

interface ConnectionPoolInterface extends ConnectionInterface
{
    public function getCurrentConnection(): ConnectionInterface;

    public function useConnection(string $connectionName): ConnectionPoolInterface;

    public function withConnections(array $connections): ConnectionPoolInterface;
}