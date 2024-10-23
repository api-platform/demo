<?php

declare(strict_types=1);

namespace App\BookRepository;

use App\Entity\Book;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GutendexBookRepository implements RestrictedBookRepositoryInterface
{
    public function __construct(
        private HttpClientInterface $gutendexClient,
        private DecoderInterface $decoder,
    ) {
    }

    public function supports(string $url): bool
    {
        return str_starts_with($url, 'https://gutendex.com');
    }

    public function find(string $url): ?Book
    {
        $options = ['headers' => ['Accept' => 'application/json']];
        $response = $this->gutendexClient->request('GET', $url, $options);
        if (200 !== $response->getStatusCode()) {
            return null;
        }

        $book = new Book();

        $data = $this->decoder->decode($response->getContent(), 'json');
        $book->title = $data['title'];
        $book->author = $data['authors'][0]['name'] ?? null;

        return $book;
    }
}
