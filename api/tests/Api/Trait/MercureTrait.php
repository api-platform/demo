<?php

declare(strict_types=1);

namespace App\Tests\Api\Trait;

use ApiPlatform\Symfony\Bundle\Test\Constraint\MatchesJsonSchema;
use Symfony\Component\Mercure\Debug\TraceableHub;
use Symfony\Component\Mercure\HubRegistry;
use Symfony\Component\Mercure\Update;

/**
 * todo Merge in ApiTestAssertionsTrait.
 */
trait MercureTrait
{
    use SerializerTrait;

    /**
     * @return Update[]
     */
    public static function getMercureMessages(string $hubName = null): array
    {
        return array_map(fn (array $update) => $update['object'], static::getMercureHub($hubName)->getMessages());
    }

    public static function getMercureMessage(int $index = 0, string $hubName = null): ?Update
    {
        return static::getMercureMessages($hubName)[$index] ?? null;
    }

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

    /**
     * @throws \JsonException
     */
    public static function assertMercureUpdateMatchesJsonSchema(Update $update, array $topics, array|object|string $jsonSchema = '', bool $private = false, string $id = null, string $type = null, int $retry = null, string $message = ''): void
    {
        static::assertSame($topics, $update->getTopics(), $message);
        static::assertThat(json_decode($update->getData(), true, \JSON_THROW_ON_ERROR), new MatchesJsonSchema($jsonSchema), $message);
        static::assertSame($private, $update->isPrivate(), $message);
        static::assertSame($id, $update->getId(), $message);
        static::assertSame($type, $update->getType(), $message);
        static::assertSame($retry, $update->getRetry(), $message);
    }
}
