<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Connection;

interface ConnectionInterface
{
    public function getBaseUri(string $alternative = null): string;
}