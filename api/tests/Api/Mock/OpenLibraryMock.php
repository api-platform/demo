<?php

declare(strict_types=1);

namespace App\Tests\Api\Mock;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[AsDecorator(decorates: 'open_library.client')]
final class OpenLibraryMock extends MockHttpClient
{
    private const array RESPONSES = [
        'https://openlibrary.org/books/OL2055137M.json' => [
            'key' => '/books/OL2055137M',
            'title' => 'Hyperion',
            'authors' => [['key' => '/authors/OL235668A']],
        ],
        'https://openlibrary.org/authors/OL235668A.json' => [
            'key' => '/authors/OL235668A',
            'name' => 'Dan Simmons',
        ],
    ];

    public function __construct()
    {
        parent::__construct($this->handleRequest(...), 'https://openlibrary.org/');
    }

    private function handleRequest(string $method, string $url): JsonMockResponse
    {
        if ('GET' !== $method || !isset(self::RESPONSES[$url])) {
            throw new \UnexpectedValueException(\sprintf('Mock not implemented: %s %s', $method, $url));
        }

        return new JsonMockResponse(self::RESPONSES[$url]);
    }
}
