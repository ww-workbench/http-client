<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Pool;

use InvalidArgumentException;
use WebWizardry\Http\Client\HttpClient;

final readonly class ClientPool
{
    public function __construct(
        private array $connections
    ) {}

    public function get(string $name): HttpClient
    {
        if (!isset($this->connections[$name])) {
            throw new InvalidArgumentException("Unknown connection name '{$name}'");
        }
        return $this->connections[$name];
    }
}