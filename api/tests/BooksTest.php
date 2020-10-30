<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\Book;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class BooksTest extends ApiTestCase
{
    // This trait provided by HautelookAliceBundle will take care of refreshing the database content to put it in a known state between every tests
    use RefreshDatabaseTrait;

    private Client $client;

    protected function setup(): void
    {
        $this->client = static::createClient();
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
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/books?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/books?page=1',
                'hydra:last' => '/books?page=4',
                'hydra:next' => '/books?page=2',
            ],
        ]);

        // It works because the API returns test fixtures loaded by Alice
        self::assertCount(30, $response->toArray()['hydra:member']);

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

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Book',
            '@type' => 'http://schema.org/Book',
            'isbn' => '0099740915',
            'title' => 'The Handmaid\'s Tale',
            'description' => 'Brilliantly conceived and executed, this powerful evocation of twenty-first century America gives full rein to Margaret Atwood\'s devastating irony, wit and astute perception.',
            'author' => 'Margaret Atwood',
            'publicationDate' => '1985-07-31T00:00:00+00:00',
            'reviews' => [],
        ]);
        self::assertRegExp('~^/books/[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$~', $response->toArray()['@id']);
        self::assertMatchesResourceItemJsonSchema(Book::class);
    }

    public function testCreateInvalidBook(): void
    {
        $this->client->request('POST', '/books', ['json' => [
            'isbn' => 'invalid',
        ]]);

        self::assertResponseStatusCodeSame(400);
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
        $iri = $this->findIriBy(Book::class, ['isbn' => '9786644879585']);
        $this->client->request('PUT', $iri, ['json' => [
            'title' => 'updated title',
        ]]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id' => $iri,
            'isbn' => '9786644879585',
            'title' => 'updated title',
        ]);
    }

    public function testDeleteBook(): void
    {
        $token = $this->login();
        $client = static::createClient();
        $iri = $this->findIriBy(Book::class, ['isbn' => '9786644879585']);
        $client->request('DELETE', $iri, ['auth_bearer' => $token]);

        self::assertResponseStatusCodeSame(204);
        self::assertNull(
            // Through the container, you can access all your services from the tests, including the ORM, the mailer, remote API clients...
            static::$container->get('doctrine')->getRepository(Book::class)->findOneBy(['isbn' => '9786644879585'])
        );
    }

    public function testGenerateCover(): void
    {
        $book = static::$container->get('doctrine')->getRepository(Book::class)->findOneBy(['isbn' => '9786644879585']);
        $this->client->request('PUT', static::$container->get('api_platform.router')->generate('api_books_generate_cover_item', ['id' => $book->getId()]), [
            'json' => [],
        ]);

        self::assertResponseIsSuccessful();
        self::assertEquals(
            1,
            static::$container->get('messenger.receiver_locator')->get('doctrine')->getMessageCount(),
            'No message has been sent.'
        );
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
