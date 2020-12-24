<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\Book;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

final class ReviewsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private Client $client;
    private const ISBN = '9786644879585';

    protected function setup(): void
    {
        $this->client = static::createClient();
    }

    public function testFilterReviewsByBook(): void
    {
        $iri = $this->findIriBy(Book::class, ['isbn' => self::ISBN]);
        $response = $this->client->request('GET', "/reviews?book=$iri");
        self::assertCount(2, $response->toArray()['hydra:member']);
    }

    public function testBookSubresource(): void
    {
        $iri = $this->findIriBy(Book::class, ['isbn' => self::ISBN]);
        $response = $this->client->request('GET', "$iri/reviews");
        self::assertCount(2, $response->toArray()['hydra:member']);
    }

    public function testCreateInvalidReviewWithInvalidBody(): void
    {
        $iri = $this->findIriBy(Book::class, ['isbn' => self::ISBN]);
        $this->client->request('POST', '/reviews', ['json' => [
            'body' => '',
            'rating' => 3,
            'book' => $iri,
            'author' => null,
            'publicationDate' => null,
        ]]);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'body: This value should not be blank.',
            'violations' => [
                [
                    'propertyPath' => 'body',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }

    /**
     * @see https://github.com/api-platform/demo/issues/164
     */
    public function testCreateInvalidReviewWithoutRating(): void
    {
        $iri = $this->findIriBy(Book::class, ['isbn' => self::ISBN]);
        $this->client->request('POST', '/reviews', ['json' => [
            'body' => 'bonjour',
            //'rating' => '', // missing rating
            'book' => $iri,
            'author' => 'COil',
            'publicationDate' => null,
        ]]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'rating: This value should not be blank.',
        ]);
    }

    public function testCreateInvalidReviewWithInvalidRating(): void
    {
        $iri = $this->findIriBy(Book::class, ['isbn' => self::ISBN]);
        $this->client->request('POST', '/reviews', ['json' => [
            'body' => 'bonjour',
            'rating' => 6,
            'book' => $iri,
            'author' => 'COil',
            'publicationDate' => null,
        ]]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'rating: This value should be between 0 and 5.',
        ]);
    }

    public function testCreateInvalidReviewWithInvalidBook(): void
    {
        $this->client->request('POST', '/reviews', ['json' => [
            'body' => '',
            'rating' => 0,
            'book' => null,
            'author' => '',
            'publicationDate' => null,
        ]]);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Expected IRI or nested document for attribute "book", "NULL" given.',
        ]);

        $this->client->request('POST', '/reviews', ['json' => [
            'body' => '',
            'rating' => 0,
            'book' => '/invalid/book_iri',
            'author' => '',
            'publicationDate' => null,
        ]]);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Invalid IRI "/invalid/book_iri".',
        ]);
    }
}
