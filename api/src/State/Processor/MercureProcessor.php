<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Metadata\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mercure\Exception\InvalidArgumentException;
use Symfony\Component\Mercure\Exception\RuntimeException;
use Symfony\Component\Mercure\HubRegistry;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @implements ProcessorInterface<object>
 */
final readonly class MercureProcessor implements ProcessorInterface
{
    public const DATA = 'mercure_data';

    public function __construct(
        private SerializerInterface $serializer,
        private HubRegistry $hubRegistry,
        private IriConverterInterface $iriConverter,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        #[Autowire('%api_platform.formats%')]
        private array $formats
    ) {}

    /**
     * @param array{item_uri_template?: string, topics?: array, mercure_data?: string} $context
     *
     * @throws InvalidArgumentException
     * @throws ResourceClassNotFoundException
     * @throws RuntimeException
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (isset($context['item_uri_template'])) {
            $operation = $this->resourceMetadataCollectionFactory->create($data::class)->getOperation($context['item_uri_template']);
        }
        if (!isset($context['topics'])) {
            $context['topics'] = [$this->iriConverter->getIriFromResource($data, UrlGeneratorInterface::ABS_URL, $operation)];
        }
        if (!isset($context[self::DATA])) {
            $context[self::DATA] = $this->serializer->serialize(
                $data,
                key($this->formats),
                ($operation->getNormalizationContext() ?? []) + (isset($context['item_uri_template']) ? [
                    'item_uri_template' => $context['item_uri_template'],
                ] : [])
            );
        }

        $this->hubRegistry->getHub()->publish(new Update(
            topics: $context['topics'],
            data: $context[self::DATA]
        ));

        return $data;
    }
}
