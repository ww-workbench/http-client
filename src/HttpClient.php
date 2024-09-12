<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WebWizardry\Http\Client\Helper\UrlHelper;
use WebWizardry\Http\Client\Middleware\MiddlewareDispatcher;

final readonly class HttpClient implements ClientInterface
{
    private UrlHelper $baseUrl;

    public function __construct(
        UrlHelper|string             $baseUrl,
        private MiddlewareDispatcher $dispatcher,
        private ClientInterface      $transport
    ) {
        $this->baseUrl = is_string($baseUrl) ? new UrlHelper($baseUrl) : $baseUrl;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $request = $this->baseUrl->applyTo($request);
        return $this->dispatcher->dispatch($request, $this->transport);
    }

    public function withTemporaryMiddlewares(array $middlewares = null): self
    {
        if (null === $middlewares) {
            return $this;
        }

        return new self(
            $this->baseUrl->getUrl(),
            $this->dispatcher->withMiddlewares($middlewares),
            $this->transport
        );
    }
}