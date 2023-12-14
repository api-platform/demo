<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\BookFactory;
use App\DataFixtures\Factory\ReviewFactory;
use App\Enum\BookCondition;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class BookTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    private Client $client;

    protected function setup(): void
    {
        $this->client = self::createClient();
    }

    /**
     * @dataProvider getUrls
     *
     * @test
     */
    public function asAnonymousICanGetACollectionOfBooks(FactoryCollection $factory, string $url, int $hydraTotalItems): void
    {
        // Cannot use Factory as data provider because BookFactory has a service dependency
        $factory->create();

        $response = $this->client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'hydra:totalItems' => $hydraTotalItems,
        ]);
        self::assertCount(min($hydraTotalItems, 30), $response->toArray()['hydra:member']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/Book/collection.json'));
    }

    public static function getUrls(): iterable
    {
        yield 'all books' => [
            BookFactory::new()->many(35),
            '/books',
            35,
        ];
        yield 'books filtered by title' => [
            BookFactory::new()->sequence(static function () {
                yield ['title' => 'Hyperion'];
                foreach (range(1, 10) as $i) {
                    yield [];
                }
            }),
            '/books?title=yperio',
            1,
        ];
        yield 'books filtered by author' => [
            BookFactory::new()->sequence(static function () {
                yield ['author' => 'Dan Simmons'];
                foreach (range(1, 10) as $i) {
                    yield [];
                }
            }),
            '/books?author=simmons',
            1,
        ];
        yield 'books filtered by condition' => [
            BookFactory::new()->sequence(static function () {
                foreach (range(1, 100) as $i) {
                    // 33% of books are damaged
                    yield ['condition' => $i % 3 ? BookCondition::NewCondition : BookCondition::DamagedCondition];
                }
            }),
            '/books?condition=' . BookCondition::DamagedCondition->value,
            33,
        ];
    }

    /**
     * @test
     */
    public function asAdminUserICanGetACollectionOfBooksOrderedByTitle(): void
    {
        BookFactory::createOne(['title' => 'Hyperion']);
        BookFactory::createOne(['title' => 'The Wandering Earth']);
        BookFactory::createOne(['title' => 'Ball Lightning']);

        $response = $this->client->request('GET', '/books?order[title]=asc');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertEquals('Ball Lightning', $response->toArray()['hydra:member'][0]['title']);
        self::assertEquals('Hyperion', $response->toArray()['hydra:member'][1]['title']);
        self::assertEquals('The Wandering Earth', $response->toArray()['hydra:member'][2]['title']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/Book/collection.json'));
    }

    /**
     * @test
     */
    public function asAnonymousICannotGetAnInvalidBook(): void
    {
        BookFactory::createOne();

        $this->client->request('GET', '/books/invalid');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function asAnonymousICanGetABook(): void
    {
        $book = BookFactory::createOne();
        ReviewFactory::createOne(['rating' => 1, 'book' => $book]);
        ReviewFactory::createOne(['rating' => 2, 'book' => $book]);
        ReviewFactory::createOne(['rating' => 3, 'book' => $book]);
        ReviewFactory::createOne(['rating' => 4, 'book' => $book]);
        ReviewFactory::createOne(['rating' => 5, 'book' => $book]);

        $this->client->request('GET', '/books/' . $book->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@id' => '/books/' . $book->getId(),
            'book' => $book->book,
            'condition' => $book->condition->value,
            'title' => $book->title,
            'author' => $book->author,
            'reviews' => '/books/' . $book->getId() . '/reviews',
            'rating' => 3,
        ]);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/Book/item.json'));
    }
}
