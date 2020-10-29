<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\DataProvider\TopBookCollectionDataProvider;
use App\Entity\TopBook;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class TopBooksTest extends ApiTestCase
{
    // This trait provided by HautelookAliceBundle will take care of refreshing
    // the database content to put it in a known state between every tests
    use RefreshDatabaseTrait;

    /**
     * @see TopBookCollectionDataProvider::getCollection()
     */
    public function testGetCollection(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        $response = static::createClient()->request('GET', '/top_books');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/TopBook',
            '@id' => '/top_books',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/top_books?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/top_books?page=1',
                'hydra:last' => '/top_books?page=4',
                'hydra:next' => '/top_books?page=2',
            ],
        ]);

        // 30 is the number returned by TopBookCollectionDataProvider::getItemsPerPage()
        self::assertCount(30, $response->toArray()['hydra:member']);

        // Checks that the returned JSON is validated by the JSON Schema generated for this API Resource by API Platform
        // This JSON Schema is also used in the generated OpenAPI spec
        self::assertMatchesResourceCollectionJsonSchema(TopBook::class);
    }

    /**
     * Nominal case.
     *
     * @see TopBookItemDataProvider::getItem()
     */
    public function testGetItem(): void
    {
        static::createClient()->request('GET', '/top_books/1');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonEquals([
            '@context' => '/contexts/TopBook',
            '@id' => '/top_books/1',
            '@type' => 'TopBook',
            'id' => 1,
            'title' => "Depuis l'au-delà",
            'author' => 'Werber Bernard',
            'part' => '',
            'place' => 'F WER',
            'borrowCount' => 9,
        ]);

        self::assertMatchesResourceItemJsonSchema(TopBook::class);
    }

    /**
     * Error case n°1: invalid identifier.
     *
     * @see TopBookItemDataProvider::checkId()
     */
    public function testGetItemErrorIdIsNotAnInteger(): void
    {
        static::createClient()->request('GET', '/top_books/foo');
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertMatchesResourceItemJsonSchema(TopBook::class);
    }

    /**
     * Error case n°2: out of range identifier.
     *
     * @see TopBookItemDataProvider::checkId()
     */
    public function testGetItemErrorIdIsOutOfRange(): void
    {
        static::createClient()->request('GET', '/top_books/101');
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertMatchesResourceItemJsonSchema(TopBook::class);
    }
}
