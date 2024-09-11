<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Curl;

use Psr\Http\Message\ResponseInterface;
use InvalidArgumentException;

final class CurlResponseHelper
{
    public function __construct(
        private ResponseInterface $response
    ) {}

    public function setStatus(string $status_line): self
    {
        $status_parts = explode(' ', $status_line, 3);
        $parts_count  = count($status_parts);

        if ($parts_count < 2 || !str_starts_with(strtoupper($status_parts[0]), 'HTTP/')) {
            throw new InvalidArgumentException("'$status_line' is not a valid HTTP status line");
        }

        $reason_phrase = ($parts_count > 2 ? $status_parts[2] : '');

        $this->response = $this->response
            ->withStatus((int)$status_parts[1], $reason_phrase)
            ->withProtocolVersion(substr($status_parts[0], 5));

        return $this;
    }

    public function addHeader(string $header_line): self
    {
        $header_parts = explode(':', $header_line, 2);

        if (count($header_parts) !== 2) {
            throw new InvalidArgumentException("'$header_line' is not a valid HTTP header line");
        }

        $header_name  = trim($header_parts[0]);
        $header_value = trim($header_parts[1]);

        if ($this->response->hasHeader($header_name)) {
            $this->response = $this->response->withAddedHeader($header_name, $header_value);
        } else {
            $this->response = $this->response->withHeader($header_name, $header_value);
        }

        return $this;
    }

    public function writeBody($data): int
    {
        return $this->response->getBody()->write($data);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function export(): ResponseInterface
    {
        $response = $this->getResponse();
        $response->getBody()->seek(0);
        return $response;
    }
}