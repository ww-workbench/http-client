<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client;

use Psr\Http\Client\ClientInterface;
use WebWizardry\Http\Client\Connection\ConnectionPoolInterface;

interface HttpClientInterface extends ClientInterface, ConnectionPoolInterface
{
}