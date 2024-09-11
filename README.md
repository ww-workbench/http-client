# PSR-7 совместимый HTTP(s) клиент

## Установка

``` bash
$ composer require webwizardry/http-client
```

## Использование

```php
$request = $factory->createRequest('GET', 'http://example.com');

$dispatcher = (new ClientMiddlewareDispatcher(new ClientMiddlewareFactory()))
    ->withClientMiddlewares([new HelloWorldMiddleware($factory)]);

$transport = new CurlClient($factory);

echo (new HttpClient($dispatcher, $transport))
        ->withTemporaryMiddlewares([new StripTagsMiddleware($factory)])
        ->sendRequest($request)
        ->getBody()->getContents() . "\n\n";
```