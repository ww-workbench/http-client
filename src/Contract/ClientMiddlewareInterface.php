<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Contract;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ClientMiddlewareInterface
{
    public function process(RequestInterface $request, ClientInterface $transport): ResponseInterface;
}