<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\BookFactory;
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
     */
    public function testAsAnonymousICanGetACollectionOfBooks(FactoryCollection $factory, string $url, int $hydraTotalItems): void
    {
        $factory->create();

        $response = $this->client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'hydra:totalItems' => $hydraTotalItems,
        ]);
        self::assertCount(min($hydraTotalItems, 30), $response->toArray()['hydra:member']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Book/collection.json'));
    }

    public function getUrls(): iterable
    {
        yield 'all books' => [
            BookFactory::new()->many(100),
            '/books',
            100
        ];
        yield 'books filtered by title' => [
            BookFactory::new()->sequence(function () {
                yield ['title' => 'Foundation'];
                foreach (range(1, 100) as $i) {
                    yield [];
                }
            }),
            '/books?title=ounda',
            1
        ];
        yield 'books filtered by author' => [
            BookFactory::new()->sequence(function () {
                yield ['author' => 'Isaac Asimov'];
                foreach (range(1, 100) as $i) {
                    yield [];
                }
            }),
            '/books?author=isaac',
            1
        ];
        yield 'books filtered by condition' => [
            BookFactory::new()->sequence(function () {
                foreach (range(1, 100) as $i) {
                    // 33% of books are damaged
                    yield ['condition' => $i%3 ? BookCondition::NewCondition : BookCondition::DamagedCondition];
                }
            }),
            '/books?condition='.BookCondition::DamagedCondition->value,
            33
        ];
    }

    public function testAsAnonymousICannotGetAnInvalidBook(): void
    {
        BookFactory::createOne();

        $this->client->request('GET', '/books/invalid');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAsAnonymousICanGetABook(): void
    {
        $book = BookFactory::createOne();

        $this->client->request('GET', '/books/'.$book->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@id' => '/books/'.$book->getId(),
            'book' => $book->book,
            'condition' => $book->condition->value,
            'title' => $book->title,
            'author' => $book->author,
        ]);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Book/item.json'));
    }
}
