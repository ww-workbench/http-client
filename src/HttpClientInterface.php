<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client;

use Psr\Http\Client\ClientInterface;

interface HttpClientInterface extends ClientInterface
{
    public function withTemporaryMiddlewares(array $middlewares): HttpClientInterface;
}