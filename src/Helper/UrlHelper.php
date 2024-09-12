<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Helper;

use Psr\Http\Message\RequestInterface;

final class UrlHelper
{
    private ?array $parsed = null;

    public function __construct(
        private readonly string $url
    ) {}

    public function applyTo(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri();
        $upd = false;

        if ($scheme = $this->getScheme()) {
            if ($scheme !== $uri->getScheme()) {
                $uri = $uri->withScheme($scheme);
                $upd = true;
            }
        }

        if ($host = $this->getHost()) {
            if ($host !== $uri->getHost()) {
                $uri = $uri->withHost($host);
                $upd = true;
            }
        }

        if ($port = $this->getPort()) {
            if ($port !== $uri->getPort()) {
                $uri = $uri->withPort($port);
                $upd = true;
            }
        }

        if ($path = $this->getPath()) {
            if (!str_starts_with($path, $uri->getPath())) {
                $uri->withPath(str_replace('//', '/', $path . '/' . $uri->getPath()));
                $upd = true;
            }
        }

        if ($upd) {
            $request = $request->withUri($uri);
        }

        return $request;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getScheme(): ?string
    {
        return $this->getParsed()['scheme'] ?? null;
    }

    public function getHost(): ?string
    {
        return $this->getParsed()['host'] ?? null;
    }

    public function getPort(): ?int
    {
        return $this->getParsed()['port'] ?? null;
    }

    public function getPath(): ?string
    {
        return $this->getParsed()['path'] ?? null;
    }

    public function getParsed(): array
    {
        if (null === $this->parsed) {
            $this->parsed = parse_url($this->url);
        }
        return $this->parsed;
    }
}