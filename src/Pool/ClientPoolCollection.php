<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Pool;

use InvalidArgumentException;
use WebWizardry\Http\Client\HttpClient;

final class ClientPoolCollection
{
    private string $currentPool;

    public function __construct(
        private readonly ClientPoolFactory $factory,
        string $currentPool
    ) {
        $this->usePool($currentPool);
    }

    public function usePool(string $name): void
    {
        if ($this->factory->has($name)) {
            $this->currentPool = $name;
        } else {
            throw new InvalidArgumentException("Pool '{$name}' does not exist");
        }
    }

    public function get(string $name): HttpClient
    {
        return $this->factory->get($this->currentPool)->get($name);
    }

    public function getUsedPool(): string
    {
        return $this->currentPool;
    }
}