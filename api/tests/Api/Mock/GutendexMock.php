<?php

declare(strict_types=1);

namespace App\Tests\Api\Mock;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[AsDecorator(decorates: 'gutendex.client')]
final class GutendexMock extends MockHttpClient
{
    private const array RESPONSES = [
        'https://gutendex.com/books/31547.json' => [
            'id' => 31547,
            'title' => 'Youth',
            'authors' => [['name' => 'Asimov, Isaac', 'birth_year' => 1920, 'death_year' => 1992]],
        ],
    ];

    public function __construct()
    {
        parent::__construct($this->handleRequest(...), 'https://gutendex.com/');
    }

    private function handleRequest(string $method, string $url): JsonMockResponse
    {
        if ('GET' !== $method || !isset(self::RESPONSES[$url])) {
            throw new \UnexpectedValueException(\sprintf('Mock not implemented: %s %s', $method, $url));
        }

        return new JsonMockResponse(self::RESPONSES[$url]);
    }
}
