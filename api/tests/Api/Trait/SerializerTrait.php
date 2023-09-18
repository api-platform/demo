<?php

declare(strict_types=1);

namespace App\Tests\Api\Trait;

use ApiPlatform\Metadata\Get;
use Symfony\Component\Serializer\SerializerInterface;

trait SerializerTrait
{
    public static function serialize(mixed $data, string $format, array $context = []): string
    {
        $container = static::getContainer();
        if ($container->has(SerializerInterface::class)) {
            return $container->get(SerializerInterface::class)->serialize($data, $format, $context);
        }

        static::fail('A client must have Serializer enabled to make serialization. Did you forget to require symfony/serializer?');
    }

    public static function getOperationNormalizationContext(string $resourceClass, string $operationName = null): array
    {
        if ($resourceMetadataFactoryCollection = static::getContainer()->get('api_platform.metadata.resource.metadata_collection_factory')) {
            $operation = $resourceMetadataFactoryCollection->create($resourceClass)->getOperation($operationName);
        } else {
            $operation = $operationName ? (new Get())->withName($operationName) : new Get();
        }

        return ($operation->getNormalizationContext() ?? []) + ['item_uri_template' => $operation->getUriTemplate()];
    }
}
