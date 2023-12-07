<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Book;
use App\Repository\ReviewRepository;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BookNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param ReviewRepository $repository
     */
    public function __construct(
        private RouterInterface $router,
        #[Autowire(service: ReviewRepository::class)]
        private ObjectRepository $repository
    ) {}

    /**
     * @param Book $object
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        $object->reviews = $this->router->generate('_api_/books/{bookId}/reviews{._format}_get_collection', [
            'bookId' => $object->getId(),
        ]);
        $object->rating = $this->repository->getAverageRating($object);

        return $this->normalizer->normalize($object, $format, [self::class => true] + $context);
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof Book && !isset($context[self::class]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Book::class => false,
        ];
    }
}
