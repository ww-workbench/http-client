<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use WebWizardry\Http\Client\ClientMiddlewareDispatcherInterface;
use WebWizardry\Http\Client\ClientMiddlewareFactoryInterface;
use WebWizardry\Http\Client\Middleware\ClientMiddlewareDispatcher;
use WebWizardry\Http\Client\Middleware\ClientMiddlewareFactory;

return [
    ClientMiddlewareFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(ClientMiddlewareFactory::class);
    },

    ClientMiddlewareDispatcherInterface::class => function (ContainerInterface $container) {
        return $container->get(ClientMiddlewareDispatcher::class);
    }
];