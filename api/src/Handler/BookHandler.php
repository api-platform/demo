<?php

declare(strict_types=1);

namespace App\Handler;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\UrlGeneratorInterface;
use ApiPlatform\Core\JsonLd\Serializer\ItemNormalizer;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use App\Entity\Book;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class BookHandler implements MessageHandlerInterface
{
    private $iriConverter;

    private $serializer;

    private $publisher;

    private $resourceMetadataFactory;

    private $imgflipClient;

    private $logger;

    public function __construct(
        IriConverterInterface $iriConverter,
        SerializerInterface $serializer,
        PublisherInterface $publisher,
        ResourceMetadataFactoryInterface $resourceMetadataFactory,
        Client $imgflipClient,
        LoggerInterface $logger
    ) {
        $this->iriConverter = $iriConverter;
        $this->serializer = $serializer;
        $this->publisher = $publisher;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->imgflipClient = $imgflipClient;
        $this->logger = $logger;
    }

    public function __invoke(Book $book): void
    {
        try {
            $response = $this->imgflipClient->get('/get_memes');
        } catch (ClientException $e) {
            $this->logger->error('Cannot call Imgflip API.', [
                'error' => $e->getMessage(),
            ]);

            return;
        }

        $json = $response->getBody()->getContents();
        $contents = \json_decode($json, true);
        if (JSON_ERROR_NONE !== \json_last_error()) {
            $this->logger->error('Invalid JSON from Imgflip API.', [
                'error' => \json_last_error_msg(),
                'json' => $json,
            ]);

            return;
        }

        $imageUrl = $contents['data']['memes'][\mt_rand(0, 99)]['url'];

        // Set Book.cover image in base64
        $book->cover = \sprintf(
            'data:image/%s;base64,%s',
            \pathinfo($imageUrl, PATHINFO_EXTENSION),
            \base64_encode(\file_get_contents($imageUrl))
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
