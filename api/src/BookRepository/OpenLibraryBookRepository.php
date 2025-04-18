<?php

declare(strict_types=1);

namespace App\BookRepository;

use App\Entity\Book;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class OpenLibraryBookRepository implements RestrictedBookRepositoryInterface
{
    public function __construct(
        private HttpClientInterface $openLibraryClient,
    ) {
    }

    public function supports(string $url): bool
    {
        return str_starts_with($url, 'https://openlibrary.org');
    }

    public function find(string $url): ?Book
    {
        $options = ['headers' => ['Accept' => 'application/json']];
        $response = $this->openLibraryClient->request('GET', $url, $options);
        if (200 !== $response->getStatusCode()) {
            return null;
        }

        $book = new Book();

        $data = $response->toArray();
        $book->title = $data['title'];

        $book->author = null;
        if (isset($data['authors'][0]['key'])) {
            $authorResponse = $this->openLibraryClient->request('GET', $data['authors'][0]['key'] . '.json', $options);
            $author = $authorResponse->toArray();
            if (isset($author['name'])) {
                $book->author = $author['name'];
            }
        }

        return $book;
    }
}
