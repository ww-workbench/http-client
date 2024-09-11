<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Exceptions;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Throwable;

final class RequestException extends RuntimeException implements RequestExceptionInterface
{
    private readonly RequestInterface $request;

    public function __construct(string $message, RequestInterface $request, Throwable|null $last_exception = null)
    {
        $this->request = $request;
        parent::__construct($message, 0, $last_exception);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}