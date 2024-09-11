<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Connection;

use InvalidArgumentException;

final class Connection implements ConnectionInterface
{
    public function __construct(
        private readonly string $baseUri,
        private readonly array  $alternativeUri = []
    ) {}

    public function getBaseUri(string $alternative = null): string
    {
        if ($alternative === null) {
            return $this->baseUri;
        }
        if (array_key_exists($alternative, $this->alternativeUri)) {
            return $this->alternativeUri[$alternative];
        }
        throw new InvalidArgumentException("Alternative URI \"{$alternative}\" does not exist.");
    }
}