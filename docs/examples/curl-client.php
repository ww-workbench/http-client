<?php
declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use WebWizardry\Http\Client\Curl\CurlClient;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

echo "\n\ncUrl client usage example:\n\n";

$factory = new Psr17Factory();
$request = $factory->createRequest('GET', 'http://example.com');

echo "request: " . $request->getMethod() . " " . (string)$request->getUri() . "\n";
$response = (new CurlClient($factory))->sendRequest($request);
echo "response: \n";
echo "  -- status: " . $response->getStatusCode() . "\n";
echo "  -- content: \n\n";
echo $response->getBody()->getContents() . "\n\n";
echo "  -- end \n\n\n";