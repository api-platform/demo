<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\JsonLd\Serializer\ItemNormalizer;
use ApiPlatform\Metadata\Operation\Factory\OperationMetadataFactoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class IriTransformerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const CONTEXT_KEY = 'iris_transform';

    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly OperationMetadataFactoryInterface $operationMetadataFactory
    ) {}

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var array $data */
        $data = $this->normalizer->normalize($object, $format, $context + [self::class => true]);

        $value = $context[self::CONTEXT_KEY];
        if (!\is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $property => $uriTemplate) {
            if (!isset($data[$property]) || !(\is_string($data[$property]) || isset($data[$property]['@id']))) {
                continue;
            }

            $iri = $this->iriConverter->getIriFromResource(
                $object->{$property},
                UrlGeneratorInterface::ABS_PATH,
                $this->operationMetadataFactory->create($uriTemplate)
            );

            if (\is_string($data[$property])) {
                $data[$property] = $iri;
            } elseif (isset($data[$property]['@id'])) {
                $data[$property]['@id'] = $iri;
            }
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return \is_object($data)
            && !is_iterable($data)
            && isset($context[self::CONTEXT_KEY])
            && ItemNormalizer::FORMAT === $format
            && !isset($context[self::class]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
        ];
    }
}
