<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Book;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @implements ProcessorInterface<Book, Book>
 */
final readonly class BookPersistProcessor implements ProcessorInterface
{
    /**
     * @param PersistProcessor $persistProcessor
     */
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private HttpClientInterface $client,
        private DecoderInterface $decoder
    ) {
    }

    /**
     * @param Book $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Book
    {
        $book = $this->getData($data->book);
        $data->title = $book['title'];

        $data->author = null;
        if (isset($book['authors'][0]['key'])) {
            $author = $this->getData('https://openlibrary.org' . $book['authors'][0]['key'] . '.json');
            if (isset($author['name'])) {
                $data->author = $author['name'];
            }
        }

        // save entity
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function getData(string $uri): array
    {
        return $this->decoder->decode($this->client->request(Request::METHOD_GET, $uri, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ])->getContent(), 'json');
    }
}
