<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use WebWizardry\Http\Client\Middleware\MiddlewareFactory;

return [
    MiddlewareFactory::class => function (ContainerInterface $container) {
        return new MiddlewareFactory($container);
    }
];