<?php

declare(strict_types=1);

namespace App\Handler;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\UrlGeneratorInterface;
use ApiPlatform\Core\JsonLd\Serializer\ItemNormalizer;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use App\Entity\Book;
use ProxyManager\Exception\ExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class BookHandler implements MessageHandlerInterface
{
    private IriConverterInterface $iriConverter;
    private SerializerInterface $serializer;
    private PublisherInterface $publisher;
    private ResourceMetadataFactoryInterface $resourceMetadataFactory;
    private HttpClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(
        IriConverterInterface $iriConverter,
        SerializerInterface $serializer,
        PublisherInterface $publisher,
        ResourceMetadataFactoryInterface $resourceMetadataFactory,
        HttpClientInterface $client,
        LoggerInterface $logger
    ) {
        $this->iriConverter = $iriConverter;
        $this->serializer = $serializer;
        $this->publisher = $publisher;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->client = $client;
        $this->logger = $logger;
    }

    public function __invoke(Book $book): void
    {
        try {
            $response = $this->client->request('https://api.imgflip.com/get_memes');
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Cannot call Imgflip API.', [
                'error' => $e->getMessage(),
            ]);

            return;
        }

        try {
            $contents = $response->toArray();
        } catch (ExceptionInterface $e) {
            $this->logger->error('Invalid JSON from Imgflip API.', [
                'error' => $e->getMessage(),
            ]);

            return;
        }

        $imageUrl = $contents['data']['memes'][\mt_rand(0, 99)]['url'];
        $imageContent = (string) \file_get_contents($imageUrl);

        // Set Book.cover image in base64
        $book->cover = \sprintf(
            'data:image/%s;base64,%s',
            \pathinfo($imageUrl, PATHINFO_EXTENSION),
            \base64_encode($imageContent)
        );

        // Send message to Mercure hub
        $update = new Update(
            $this->iriConverter->getIriFromItem($book, UrlGeneratorInterface::ABS_URL),
            $this->serializer->serialize(
                $book,
                ItemNormalizer::FORMAT,
                $this->resourceMetadataFactory->create(Book::class)->getItemOperationAttribute('generate_cover', 'normalizationContext', [])
            )
        );
        ($this->publisher)($update);
    }
}
