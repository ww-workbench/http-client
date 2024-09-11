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
            '<strong>Hello World!</strong>' . "\n\n" . $response->getBody()->getContents()
        ));
    }
}

$request = $factory->createRequest('GET', 'http://example.com');

$dispatcher = (new ClientMiddlewareDispatcher(new ClientMiddlewareFactory()))
    ->withClientMiddlewares([new HelloWorldMiddleware($factory)]);

$transport = new CurlClient($factory);

echo (new HttpClient($dispatcher, $transport))
        ->withTemporaryMiddlewares([new StripTagsMiddleware($factory)])
        ->sendRequest($request)
        ->getBody()->getContents() . "\n\n";