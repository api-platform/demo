<?php

declare(strict_types=1);

namespace App\Handler;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\JsonLd\Serializer\ItemNormalizer;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\Book;
use ProxyManager\Exception\ExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class BookHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly SerializerInterface $serializer,
        private readonly HubInterface $hub,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(Book $book): void
    {
        try {
            $response = $this->client->request('GET', 'https://api.imgflip.com/get_memes');
        } catch (TransportExceptionInterface $transportException) {
            $this->logger->error('Cannot call Imgflip API.', [
                'error' => $transportException->getMessage(),
            ]);

            return;
        }

        try {
            $contents = $response->toArray();
        } catch (ExceptionInterface $exception) {
            $this->logger->error('Invalid JSON from Imgflip API.', [
                'error' => $exception->getMessage(),
            ]);

            return;
        }

        $imageUrl = $contents['data']['memes'][\mt_rand(0, 99)]['url'];
        $imageContent = (string) \file_get_contents($imageUrl);

        // Set Book.cover image in base64
        $book->cover = \sprintf(
            'data:image/%s;base64,%s',
            \pathinfo((string) $imageUrl, PATHINFO_EXTENSION),
            \base64_encode($imageContent)
        );

        // Send message to Mercure hub
        $update = new Update(
            $this->iriConverter->getIriFromResource($book, UrlGeneratorInterface::ABS_URL),
            $this->serializer->serialize(
                $book,
                ItemNormalizer::FORMAT,
                $this->resourceMetadataCollectionFactory->create(Book::class)
                    ->getOperation('_api_/books/{id}/generate-cover.{_format}_put')
                    ->getNormalizationContext()
            )
        );
        $this->hub->publish($update);
    }
}
