<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use ApiPlatform\Symfony\Routing\Router;
use App\Entity\Book;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\ServiceProviderInterface;

class BooksTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private Client $client;
    private Router $router;

    public const ISBN = '9786644879585';
    public const ITEMS_PER_PAGE = 30;
    public const COUNT_WITHOUT_ARCHIVED = 100;
    public const COUNT_ARCHIVED = 1;
    public const COUNT = self::COUNT_WITHOUT_ARCHIVED + self::COUNT_ARCHIVED;

    protected function setup(): void
    {
        $this->client = static::createClient();
        $router = static::getContainer()->get('api_platform.router');
        if (!$router instanceof Router) {
            throw new \RuntimeException('api_platform.router service not found.');
        }
        $this->router = $router;
    }

    public function testGetCollection(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        $response = $this->client->request('GET', '/books');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Book',
            '@id' => '/books',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => self::COUNT,
            'hydra:view' => [
                '@id' => '/books?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/books?page=1',
                'hydra:last' => '/books?page=4',
                'hydra:next' => '/books?page=2',
            ],
        ]);

        // It works because the API returns test fixtures loaded by Alice
        self::assertCount(self::ITEMS_PER_PAGE, $response->toArray()['hydra:member']);

        static::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/books.json'));
        // Checks that the returned JSON is validated by the JSON Schema generated for this API Resource by API Platform
        // This JSON Schema is also used in the generated OpenAPI spec
        self::assertMatchesResourceCollectionJsonSchema(Book::class);
    }

    public function testCreateBook(): void
    {
        $response = $this->client->request('POST', '/books', ['json' => [
            'isbn' => '0099740915',
            'title' => 'The Handmaid\'s Tale',
            'description' => 'Brilliantly conceived and executed, this powerful evocation of twenty-first century America gives full rein to Margaret Atwood\'s devastating irony, wit and astute perception.',
            'author' => 'Margaret Atwood',
            'publicationDate' => '1985-07-31T00:00:00+00:00',
        ]]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Book',
            '@type' => 'https://schema.org/Book',
            'isbn' => '0099740915',
            'title' => 'The Handmaid\'s Tale',
            'description' => 'Brilliantly conceived and executed, this powerful evocation of twenty-first century America gives full rein to Margaret Atwood\'s devastating irony, wit and astute perception.',
            'author' => 'Margaret Atwood',
            'publicationDate' => '1985-07-31T00:00:00+00:00',
            'reviews' => [],
        ]);
        self::assertMatchesRegularExpression('~^/books/[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$~', $response->toArray()['@id']);
        self::assertMatchesResourceItemJsonSchema(Book::class);
    }

    public function testCreateInvalidBook(): void
    {
        $this->client->request('POST', '/books', ['json' => [
            'isbn' => 'invalid',
        ]]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        self::assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'isbn: This value is neither a valid ISBN-10 nor a valid ISBN-13.
title: This value should not be blank.
description: This value should not be blank.
author: This value should not be blank.
publicationDate: This value should not be null.',
        ]);
    }

    public function testUpdateBook(): void
    {
        $iri = (string) $this->findIriBy(Book::class, ['isbn' => self::ISBN]);
        $this->client->request('PUT', $iri, ['json' => [
            'title' => 'updated title',
        ]]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id' => $iri,
            'isbn' => self::ISBN,
            'title' => 'updated title',
        ]);
    }

    public function testDeleteBook(): void
    {
        $token = $this->login();
        $client = static::createClient();
        $iri = (string) $this->findIriBy(Book::class, ['isbn' => self::ISBN]);
        $client->request('DELETE', $iri, ['auth_bearer' => $token]);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertNull(
            // Through the container, you can access all your services from the tests, including the ORM, the mailer, remote API clients...
            static::getContainer()->get('doctrine')->getRepository(Book::class)->findOneBy(['isbn' => self::ISBN])
        );
    }

    public function testGenerateCover(): void
    {
        $book = static::getContainer()->get('doctrine')->getRepository(Book::class)->findOneBy(['isbn' => self::ISBN]);
        self::assertInstanceOf(Book::class, $book);
        if (!$book instanceof Book) {
            throw new \LogicException('Book not found.');
        }
        $this->client->request('PUT', $this->router->generate('api_books_generate_cover_item', ['id' => $book->getId()]), [
            'json' => [],
        ]);

        $messengerReceiverLocator = static::getContainer()->get('messenger.receiver_locator');
        if (!$messengerReceiverLocator instanceof ServiceProviderInterface) {
            throw new \RuntimeException('messenger.receiver_locator service not found.');
        }

        self::assertResponseIsSuccessful();
        self::assertEquals(
            1,
            $messengerReceiverLocator->get('doctrine')->getMessageCount(),
            'No message has been sent.'
        );
    }

    /**
     * The filter is not applied by default on the Book collections.
     */
    public function testArchivedFilterDefault(): void
    {
        $this->client->request('GET', '/books');
        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id' => '/books',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => self::COUNT,
        ]);
    }

    public function archivedParameterProvider(): \iterator
    {
        // Only archived are returned
        yield ['true',  self::COUNT_ARCHIVED];
        yield ['1', self::COUNT_ARCHIVED];

        // Incorrect value, no filter applied
        yield ['',  self::COUNT];
        yield ['true[]',  self::COUNT];
        yield ['foobar',  self::COUNT];

        // archived items are excluded
        yield ['false',  self::COUNT_WITHOUT_ARCHIVED];
        yield ['0',  self::COUNT_WITHOUT_ARCHIVED];
    }

    /**
     * @dataProvider archivedParameterProvider
     */
    public function testArchivedFilterParameter(string $archivedValue, int $count): void
    {
        $this->client->request('GET', '/books?archived='.$archivedValue);
        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id' => '/books',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => $count,
        ]);
    }

    private function login(): string
    {
        $response = static::createClient()->request('POST', '/authentication_token', ['json' => [
            'email' => 'admin@example.com',
            'password' => 'admin',
        ]]);

        return $response->toArray()['token'];
    }
}
