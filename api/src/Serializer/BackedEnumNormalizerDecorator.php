<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Reproducer for https://github.com/api-platform/demo/issues/601.
 *
 * This decorator simulates the behavior introduced in symfony/serializer by
 * Symfony PR #62574 (commit 35b1aec), which improves BackedEnumNormalizer
 * error messages. That change was temporarily in v8.0.5 (then reverted) and
 * will ship in Symfony 8.1.
 *
 * The improved normalizer distinguishes two error cases:
 *   1. Type mismatch: data is not int/string → expectedTypes = [$backingType]
 *   2. Invalid value: data is the right type but not a valid enum case
 *      → expectedTypes = null, message lists valid values
 *
 * Case 2 exposes a bug in api-platform/state's DeserializeProvider which does
 * not handle null/empty expectedTypes, producing: "This value should be of type ."
 *
 * @todo Remove this decorator once Symfony 8.1 is adopted and the upstream
 *       API Platform bug is fixed.
 */
#[AsDecorator('serializer.normalizer.backed_enum')]
final class BackedEnumNormalizerDecorator implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private readonly BackedEnumNormalizer $inner,
    ) {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): int|string
    {
        return $this->inner->normalize($data, $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->inner->supportsNormalization($data, $format, $context);
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!is_subclass_of($type, \BackedEnum::class)) {
            return $this->inner->denormalize($data, $type, $format, $context);
        }

        $backingType = (new \ReflectionEnum($type))->getBackingType()?->getName();

        // Case 1: Type mismatch — data is not the expected backing type
        if (null === $data || ('int' === $backingType && !\is_int($data)) || ('string' === $backingType && !\is_string($data))) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                \sprintf('The data must be of type %s.', $backingType),
                $data,
                [$backingType],
                $context['deserialization_path'] ?? null,
                true,
            );
        }

        // Case 2: Invalid value — right type but not a valid enum case
        try {
            return $type::from($data);
        } catch (\ValueError|\TypeError $e) {
            $validValues = array_map(
                static fn (\BackedEnum $case): string => \is_string($case->value)
                    ? \sprintf("'%s'", $case->value)
                    : (string) $case->value,
                $type::cases(),
            );

            throw new NotNormalizableValueException(
                message: \sprintf('The data must be one of the following values: %s', implode(', ', $validValues)),
                previous: $e,
                path: $context['deserialization_path'] ?? null,
                useMessageForUser: true,
            );
        }
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $this->inner->supportsDenormalization($data, $type, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->inner->getSupportedTypes($format);
    }
}
