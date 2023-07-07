<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Book;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BookNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private RouterInterface $router)
    {
    }

    /**
     * @param Book $object
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        // set "reviews" on the object, and let the serializer decide if it must be exposed or not
        $object->reviews = $this->router->generate('_api_/books/{bookId}/reviews{._format}_get_collection', [
            'bookId' => $object->getId(),
        ]);

        return $this->normalizer->normalize($object, $format, $context + [self::class => true]);
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof Book && !isset($context[self::class]);
    }
}
