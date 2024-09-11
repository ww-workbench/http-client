<?php
declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WebWizardry\Di\ContainerBuilder;
use WebWizardry\Http\Client\ClientMiddlewareInterface;
use WebWizardry\Http\Client\Connection\ConnectionPool;
use WebWizardry\Http\Client\Curl\CurlClient;
use WebWizardry\Http\Client\HttpClient;
use WebWizardry\Http\Client\Middleware\ClientMiddlewareDispatcher;
use WebWizardry\Http\Client\Middleware\ClientMiddlewareFactory;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$factory = new Psr17Factory();

class StripTagsMiddleware implements ClientMiddlewareInterface
{
    public function __construct(
        private readonly \Psr\Http\Message\StreamFactoryInterface $streamFactory,
    ){}

    public function process(RequestInterface $request, ClientInterface $transport): ResponseInterface
    {
        $response = $transport->sendRequest($request);
        return $response->withBody($this->streamFactory->createStream(
            strip_tags($response->getBody()->getContents())
        ));
    }
}

class HelloWorldMiddleware implements ClientMiddlewareInterface
{
    public function __construct(
        private readonly \Psr\Http\Message\StreamFactoryInterface $streamFactory,
    ){}

    public function process(RequestInterface $request, ClientInterface $transport): ResponseInterface
    {
        $response = $transport->sendRequest($request);
        return $response->withBody($this->streamFactory->createStream(
            'Hello World!'
        ));
    }
}

$container = (new ContainerBuilder())->build();
$dispatcher = (new ClientMiddlewareDispatcher(new ClientMiddlewareFactory($container)));
$dispatcher2 = $dispatcher->withClientMiddlewares([new StripTagsMiddleware($factory)]);
$transport = new CurlClient($factory);


$request = $factory->createRequest('GET', 'http://example.com');

echo "\n\nWITH middleware\n";
echo "request: " . $request->getMethod() . " " . (string)$request->getUri() . "\n";
$response = (new HttpClient($dispatcher2, $transport))->sendRequest($request);
echo "response: \n";
echo "  -- status: " . $response->getStatusCode() . "\n";
echo "  -- content: \n\n";
echo $response->getBody()->getContents() . "\n\n";
echo "  -- end \n\n\n";

echo "\n\nWITHOUT middleware\n";
echo "request: " . $request->getMethod() . " " . (string)$request->getUri() . "\n";
$response = (new HttpClient($dispatcher, $transport))->sendRequest($request);
echo "response: \n";
echo "  -- status: " . $response->getStatusCode() . "\n";
echo "  -- content: \n\n";
echo $response->getBody()->getContents() . "\n\n";
echo "  -- end \n\n\n";

echo "\n\nWITH temporary middleware\n";

echo (new HttpClient($dispatcher2, $transport))
        ->withTemporaryMiddlewares([new HelloWorldMiddleware($factory)])
        ->sendRequest($request)
        ->getBody()->getContents() . "\n\n";

echo (new HttpClient($dispatcher, $transport))
        ->withTemporaryMiddlewares([new HelloWorldMiddleware($factory)])
        ->sendRequest($request)
        ->getBody()->getContents() . "\n\n";