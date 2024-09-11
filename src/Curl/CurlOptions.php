<?php
declare(strict_types=1);

namespace WebWizardry\Http\Client\Curl;

use Psr\Http\Message\RequestInterface;
use WebWizardry\Http\Client\Exceptions\RequestException;


final class CurlOptions
{
    private static int $maxBodySize = 1024 * 1024;
    private array $options = [];

    public function __construct(
        array $options = [],
    ) {
        $options[CURLOPT_FOLLOWLOCATION] = false;
        $options[CURLOPT_HEADER]         = false;
        $options[CURLOPT_RETURNTRANSFER] = false;

        $this->options = $options;
    }

    public function build(RequestInterface $request, CurlResponseHelper $response): array
    {
        $options = $this->options;

        $options[CURLOPT_HTTP_VERSION] = $this->getProtocolVersion($request);
        $options[CURLOPT_URL] = (string) $request->getUri();
        $options[CURLOPT_HTTPHEADER] = $this->createHeaders($request, $options);

        if ($request->getUri()->getUserInfo()) {
            $options[CURLOPT_USERPWD] = $request->getUri()->getUserInfo();
        }

        $options = $this->addRequestBodyOptions($request, $options);

        $options[CURLOPT_HEADERFUNCTION] = function ($ch, $data) use ($response) {
            $cleanData = trim($data);
            if ($cleanData !== '') {
                if (str_starts_with(strtoupper($cleanData), 'HTTP/')) {
                    $response->setStatus($cleanData);
                } else {
                    $response->addHeader($cleanData);
                }
            }

            return strlen($data);
        };

        $options[CURLOPT_WRITEFUNCTION] = function ($ch, $data) use ($response) {
            return $response->writeBody($data);
        };

        return $options;
    }

    private function getProtocolVersion(RequestInterface $request): int
    {
        switch ($request->getProtocolVersion()) {
            case '1.0':
                return CURL_HTTP_VERSION_1_0;
            case '1.1':
                return CURL_HTTP_VERSION_1_1;
            case '2.0':
                if (defined('CURL_HTTP_VERSION_2_0')) {
                    return CURL_HTTP_VERSION_2_0;
                }
                throw new RequestException('libcurl 7.33 required for HTTP 2.0', $request);
        }
        return CURL_HTTP_VERSION_NONE;
    }

    private function createHeaders(RequestInterface $request, array $options): array
    {
        $headers         = [];
        $request_headers = $request->getHeaders();

        foreach ($request_headers as $name => $values) {
            $header = strtoupper($name);

            // cURL does not support 'Expect-Continue', skip all 'EXPECT' headers
            if ($header === 'EXPECT') {
                continue;
            }

            if ($header === 'CONTENT-LENGTH') {
                if (array_key_exists(CURLOPT_POSTFIELDS, $options)) {
                    $values = [strlen($options[CURLOPT_POSTFIELDS])];
                } elseif (!array_key_exists(CURLOPT_READFUNCTION, $options)) {
                    // Force content length to '0' if body is empty
                    $values = [0];
                }
            }

            foreach ($values as $value) {
                $headers[] = $name . ': ' . $value;
            }
        }

        // Although cURL does not support 'Expect-Continue', it adds the 'Expect'
        // header by default, so we need to force 'Expect' to empty.
        $headers[] = 'Expect:';

        return $headers;
    }

    private function addRequestBodyOptions(RequestInterface $request, array $options): array
    {
        $httpMethodsWithoutBody = ['GET', 'HEAD', 'TRACE',];

        if (!in_array($request->getMethod(), $httpMethodsWithoutBody, true)) {
            $body      = $request->getBody();
            $bodySize = $body->getSize();

            if ($bodySize !== 0) {
                if ($body->isSeekable()) {
                    $body->rewind();
                }

                if ($bodySize === null || $bodySize > self::$maxBodySize) {
                    $options[CURLOPT_UPLOAD] = true;

                    if ($bodySize !== null) {
                        $options[CURLOPT_INFILESIZE] = $bodySize;
                    }

                    $options[CURLOPT_READFUNCTION] = function ($ch, $fd, $len) use ($body) {
                        return $body->read($len);
                    };
                } else {
                    $options[CURLOPT_POSTFIELDS] = (string)$body;
                }
            }
        }

        if ($request->getMethod() === 'HEAD') {
            $options[CURLOPT_NOBODY] = true;
        } elseif ($request->getMethod() !== 'GET') {
            $options[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
        }

        return $options;
    }
}