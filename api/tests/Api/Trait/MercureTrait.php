<?php

declare(strict_types=1);

namespace App\Tests\Api\Trait;

use Symfony\Component\Mercure\Debug\TraceableHub;
use Symfony\Component\Mercure\HubRegistry;

/**
 * todo waiting for https://github.com/api-platform/core/pull/5834
 */
trait MercureTrait
{
    private static function getMercureRegistry(): HubRegistry
    {
        $container = static::getContainer();
        if ($container->has(HubRegistry::class)) {
            return $container->get(HubRegistry::class);
        }

        static::fail('A client must have Mercure enabled to make update assertions. Did you forget to require symfony/mercure?');
    }

    private static function getMercureHub(string $name = null): TraceableHub
    {
        $hub = static::getMercureRegistry()->getHub($name);
        if (!$hub instanceof TraceableHub) {
            static::fail('Debug mode must be enabled to make Mercure update assertions.');
        }

        return $hub;
    }
}
