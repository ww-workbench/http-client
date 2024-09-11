<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Curl;

use CurlHandle;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use WebWizardry\Http\Client\Exceptions\NetworkException;
use WebWizardry\Http\Client\Exceptions\RequestException;

final class CurlClient implements ClientInterface
{
    private readonly CurlOptions $options;
    private ?CurlHandle $ch = null;

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        array $options = []
    ){
        $this->options = new CurlOptions($options);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $response = new CurlResponseHelper($this->responseFactory->createResponse());
        $this->resetCurlHandler();
        curl_setopt_array($this->ch, $this->options->build($request, $response));
        curl_exec($this->ch);
        $this->handleCurlError($request);
        return $response->export();
    }

    private function handleCurlError(RequestInterface $request): void
    {
        switch (curl_errno($this->ch)) {
            case CURLE_OK:
                break;
            case CURLE_COULDNT_RESOLVE_PROXY:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_COULDNT_CONNECT:
            case CURLE_OPERATION_TIMEOUTED:
            case CURLE_SSL_CONNECT_ERROR:
                throw new NetworkException(curl_error($this->ch), $request);
            default:
                throw new RequestException(curl_error($this->ch), $request);
        }
    }

    private function resetCurlHandler(): void
    {
        if ($this->ch instanceof CurlHandle) {
            if (function_exists('curl_reset')) {
                curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, null);
                curl_setopt($this->ch, CURLOPT_READFUNCTION, null);
                curl_setopt($this->ch, CURLOPT_WRITEFUNCTION, null);
                curl_setopt($this->ch, CURLOPT_PROGRESSFUNCTION, null);
                curl_reset($this->ch);
            } else {
                curl_close($this->ch);
                $this->ch = curl_init();
            }
        } else {
            $this->ch = curl_init();
        }
    }
}