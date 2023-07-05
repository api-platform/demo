<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Book;
use App\Exception\InvalidBnfResponseException;
use App\Repository\BookRepository;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class BookPersistProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: BookRepository::class)]
        private ObjectRepository $repository,
        private HttpClientInterface $bnfClient,
        private DecoderInterface $decoder
    ) {
    }

    /**
     * @param Book $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Book
    {
        // call BNF API
        $response = $this->bnfClient->request(Request::METHOD_GET, $data->book);
        $results = $this->decoder->decode($response->getContent(), 'xml');
        if (!$title = $results['notice']['record']['metadata']['oai_dc:dc']['dc:title'] ?? null) {
            throw new InvalidBnfResponseException('Missing property "dc:title" in BNF API response.');
        }
        if (!$publisher = $results['notice']['record']['metadata']['oai_dc:dc']['dc:publisher'] ?? null) {
            throw new InvalidBnfResponseException('Missing property "dc:publisher" in BNF API response.');
        }
        $data->title = $title;
        $data->author = $publisher;

        // save entity
        $this->repository->save($data, true);

        return $data;
    }
}
