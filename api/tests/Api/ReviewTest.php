<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\BookFactory;
use App\DataFixtures\Factory\ReviewFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Book;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\ReviewRepository;
use App\Tests\Api\Trait\MercureTrait;
use App\Tests\Api\Trait\SecurityTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ReviewTest extends ApiTestCase
{
    use Factories;
    use MercureTrait;
    use ResetDatabase;
    use SecurityTrait;

    private Client $client;

    protected function setup(): void
    {
        $this->client = self::createClient();
    }

    /**
     * Filters are disabled on /books/{bookId}/reviews.
     *
     * @dataProvider getUrls
     */
    public function testAsAnonymousICanGetACollectionOfBookReviewsWithoutFilters(FactoryCollection $factory, string|callable $url, int $hydraTotalItems, int $totalHydraMember = 30): void
    {
        $factory->create();

        if (is_callable($url)) {
            $url = $url();
        }

        $response = $this->client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'hydra:totalItems' => $hydraTotalItems,
        ]);
        self::assertCount(min($hydraTotalItems, $totalHydraMember), $response->toArray()['hydra:member']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Review/collection.json'));
    }

    public function getUrls(): iterable
    {
        yield 'all book reviews' => [
            ReviewFactory::new()->sequence(function () {
                $book = BookFactory::createOne(['title' => 'Foundation']);
                foreach (range(1, 100) as $i) {
                    yield ['book' => $book];
                }
            }),
            static function (): string {
                /** @var Book[] $books */
                $books = BookFactory::findBy(['title' => 'Foundation']);

                return '/books/'.$books[0]->getId().'/reviews';
            },
            100,
        ];
        yield 'all book reviews using itemsPerPage' => [
            ReviewFactory::new()->sequence(function () {
                $book = BookFactory::createOne(['title' => 'Foundation']);
                foreach (range(1, 100) as $i) {
                    yield ['book' => $book];
                }
            }),
            static function (): string {
                /** @var Book[] $books */
                $books = BookFactory::findBy(['title' => 'Foundation']);

                return '/books/'.$books[0]->getId().'/reviews?itemsPerPage=10';
            },
            100,
            10,
        ];
        yield 'book reviews filtered by rating' => [
            ReviewFactory::new()->sequence(function () {
                $book = BookFactory::createOne(['title' => 'Foundation']);
                foreach (range(1, 100) as $i) {
                    // 33% of reviews are rated 5
                    yield ['book' => $book, 'rating' => $i % 3 ? 3 : 5];
                }
            }),
            static function (): string {
                /** @var Book[] $books */
                $books = BookFactory::findBy(['title' => 'Foundation']);

                return '/books/'.$books[0]->getId().'/reviews?rating=5';
            },
            100,
        ];
        yield 'book reviews filtered by user' => [
            ReviewFactory::new()->sequence(function () {
                $book = BookFactory::createOne(['title' => 'Foundation']);
                yield ['book' => $book, 'user' => UserFactory::createOne(['email' => 'user@example.com'])];
                foreach (range(1, 99) as $i) {
                    yield ['book' => $book, 'user' => UserFactory::createOne()];
                }
            }),
            static function (): string {
                /** @var Book[] $books */
                $books = BookFactory::findBy(['title' => 'Foundation']);
                /** @var User[] $users */
                $users = UserFactory::findBy(['email' => 'user@example.com']);

                return '/books/'.$books[0]->getId().'/reviews?user=/users/'.$users[0]->getId();
            },
            100,
        ];
    }

    public function testAsAnonymousICannotAddAReviewOnABook(): void
    {
        $book = BookFactory::createOne();

        $this->client->request('POST', '/books/'.$book->getId().'/reviews', [
            'json' => [
                'body' => 'Very good book!',
                'rating' => 5,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Full authentication is required to access this resource.',
        ]);
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testAsAUserICannotAddAReviewOnABookWithInvalidData(array $data, int $statusCode, array $expected): void
    {
        $book = BookFactory::createOne();

        $token = $this->generateToken([
            'email' => UserFactory::createOne()->email,
        ]);

        $this->client->request('POST', '/books/'.$book->getId().'/reviews', [
            'auth_bearer' => $token,
            'json' => $data,
        ]);

        self::assertResponseStatusCodeSame($statusCode);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains($expected);
    }

    public function getInvalidData(): iterable
    {
        $uuid = Uuid::v7()->__toString();

        yield 'empty data' => [
            [],
            Response::HTTP_UNPROCESSABLE_ENTITY,
            [
                '@context' => '/contexts/ConstraintViolationList',
                '@type' => 'ConstraintViolationList',
                'hydra:title' => 'An error occurred',
                'violations' => [
                    [
                        'propertyPath' => 'body',
                        'message' => 'This value should not be blank.',
                    ],
                    [
                        'propertyPath' => 'rating',
                        'message' => 'This value should not be null.',
                    ],
                ],
            ],
        ];
        yield 'invalid book data' => [
            [
                'book' => 'invalid book',
                'body' => 'Very good book!',
                'rating' => 5,
            ],
            Response::HTTP_BAD_REQUEST,
            [
                '@context' => '/contexts/Error',
                '@type' => 'hydra:Error',
                'hydra:title' => 'An error occurred',
                'hydra:description' => 'Invalid IRI "invalid book".',
            ],
        ];
        yield 'invalid book identifier' => [
            [
                'book' => '/books/'.$uuid,
                'body' => 'Very good book!',
                'rating' => 5,
            ],
            Response::HTTP_BAD_REQUEST,
            [
                '@context' => '/contexts/Error',
                '@type' => 'hydra:Error',
                'hydra:title' => 'An error occurred',
                'hydra:description' => 'Item not found for "/books/'.$uuid.'".',
            ],
        ];
    }

    public function testAsAUserICannotAddAReviewWithValidDataOnAnInvalidBook(): void
    {
        $book = BookFactory::createOne();
        ReviewFactory::createMany(5, ['book' => $book]);
        $user = UserFactory::createOne();
        self::getMercureHub()->reset();

        $token = $this->generateToken([
            'email' => $user->email,
        ]);

        $this->client->request('POST', '/books/invalid/reviews', [
            'auth_bearer' => $token,
            'json' => [
                'body' => 'Very good book!',
                'rating' => 5,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Invalid identifier value or configuration.',
        ]);
    }

    /**
     * @group mercure
     */
    public function testAsAUserICanAddAReviewOnABook(): void
    {
        $book = BookFactory::createOne();
        ReviewFactory::createMany(5, ['book' => $book]);
        $user = UserFactory::createOne();
        self::getMercureHub()->reset();

        $token = $this->generateToken([
            'email' => $user->email,
        ]);

        $response = $this->client->request('POST', '/books/'.$book->getId().'/reviews', [
            'auth_bearer' => $token,
            'json' => [
                'body' => 'Very good book!',
                'rating' => 5,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'book' => '/books/'.$book->getId(),
            'user' => [
                '@id' => '/users/'.$user->getId(),
            ],
            'body' => 'Very good book!',
            'rating' => 5,
        ]);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Review/item.json'));
        // if I add a review on a book with reviews, it doesn't erase the existing reviews
        $reviews = self::getContainer()->get(ReviewRepository::class)->findBy(['book' => $book->object()]);
        self::assertCount(6, $reviews);
        $id = preg_replace('/^.*\/(.+)$/', '$1', $response->toArray()['@id']);
        /** @var Review $review */
        $review = self::getContainer()->get(ReviewRepository::class)->find($id);
        self::assertCount(2, self::getMercureMessages());
        self::assertMercureUpdateMatchesJsonSchema(
            update: self::getMercureMessage(),
            topics: ['http://localhost/admin/reviews/'.$review->getId()],
            jsonSchema: file_get_contents(__DIR__.'/Admin/schemas/Review/item.json')
        );
        self::assertMercureUpdateMatchesJsonSchema(
            update: self::getMercureMessage(1),
            topics: ['http://localhost/books/'.$book->getId().'/reviews/'.$review->getId()],
            jsonSchema: file_get_contents(__DIR__.'/schemas/Review/item.json')
        );
    }

    public function testAsAnonymousICannotGetAnInvalidReview(): void
    {
        $book = BookFactory::createOne();

        $this->client->request('GET', '/books/'.$book->getId().'/reviews/invalid');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'This route does not aim to be called.',
        ]);
    }

    public function testAsAnonymousICanGetABookReview(): void
    {
        $review = ReviewFactory::createOne();

        $this->client->request('GET', '/books/'.$review->book->getId().'/reviews/'.$review->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'This route does not aim to be called.',
        ]);
    }

    public function testAsAnonymousICannotUpdateABookReview(): void
    {
        $review = ReviewFactory::createOne();

        $this->client->request('PATCH', '/books/'.$review->book->getId().'/reviews/'.$review->getId(), [
            'json' => [
                'body' => 'Very good book!',
                'rating' => 5,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Full authentication is required to access this resource.',
        ]);
    }

    public function testAsAUserICannotUpdateABookReviewOfAnotherUser(): void
    {
        $review = ReviewFactory::createOne(['user' => UserFactory::createOne()]);

        $token = $this->generateToken([
            'email' => UserFactory::createOne()->email,
        ]);

        $this->client->request('PATCH', '/books/'.$review->book->getId().'/reviews/'.$review->getId(), [
            'auth_bearer' => $token,
            'json' => [
                'body' => 'Very good book!',
                'rating' => 5,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Access Denied.',
        ]);
    }

    public function testAsAUserICannotUpdateAnInvalidBookReview(): void
    {
        $book = BookFactory::createOne();

        $token = $this->generateToken([
            'email' => UserFactory::createOne()->email,
        ]);

        $this->client->request('PATCH', '/books/'.$book->getId().'/reviews/invalid', [
            'auth_bearer' => $token,
            'json' => [
                'body' => 'Very good book!',
                'rating' => 5,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @group mercure
     */
    public function testAsAUserICanUpdateMyBookReview(): void
    {
        $review = ReviewFactory::createOne();
        self::getMercureHub()->reset();

        $token = $this->generateToken([
            'email' => $review->user->email,
        ]);

        $this->client->request('PATCH', '/books/'.$review->book->getId().'/reviews/'.$review->getId(), [
            'auth_bearer' => $token,
            'json' => [
                'body' => 'Very good book!',
                'rating' => 5,
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'body' => 'Very good book!',
            'rating' => 5,
        ]);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Review/item.json'));
        self::assertCount(2, self::getMercureMessages());
        self::assertMercureUpdateMatchesJsonSchema(
            update: self::getMercureMessage(),
            topics: ['http://localhost/admin/reviews/'.$review->getId()],
            jsonSchema: file_get_contents(__DIR__.'/Admin/schemas/Review/item.json')
        );
        self::assertMercureUpdateMatchesJsonSchema(
            update: self::getMercureMessage(1),
            topics: ['http://localhost/books/'.$review->book->getId().'/reviews/'.$review->getId()],
            jsonSchema: file_get_contents(__DIR__.'/schemas/Review/item.json')
        );
    }

    public function testAsAnonymousICannotDeleteABookReview(): void
    {
        $review = ReviewFactory::createOne();

        $this->client->request('DELETE', '/books/'.$review->book->getId().'/reviews/'.$review->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Full authentication is required to access this resource.',
        ]);
    }

    public function testAsAUserICannotDeleteABookReviewOfAnotherUser(): void
    {
        $review = ReviewFactory::createOne(['user' => UserFactory::createOne()]);

        $token = $this->generateToken([
            'email' => UserFactory::createOne()->email,
        ]);

        $this->client->request('DELETE', '/books/'.$review->book->getId().'/reviews/'.$review->getId(), [
            'auth_bearer' => $token,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Access Denied.',
        ]);
    }

    public function testAsAUserICannotDeleteAnInvalidBookReview(): void
    {
        $book = BookFactory::createOne();

        $token = $this->generateToken([
            'email' => UserFactory::createOne()->email,
        ]);

        $this->client->request('DELETE', '/books/'.$book->getId().'/reviews/invalid', [
            'auth_bearer' => $token,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @group mercure
     */
    public function testAsAUserICanDeleteMyBookReview(): void
    {
        $review = ReviewFactory::createOne()->disableAutoRefresh();
        self::getMercureHub()->reset();
        $id = $review->getId();
        $bookId = $review->book->getId();

        $token = $this->generateToken([
            'email' => $review->user->email,
        ]);

        $response = $this->client->request('DELETE', '/books/'.$bookId.'/reviews/'.$id, [
            'auth_bearer' => $token,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertEmpty($response->getContent());
        self::assertNull(self::getContainer()->get(ReviewRepository::class)->find($id));
        self::assertCount(2, self::getMercureMessages());
        // todo how to ensure it's a delete update
        self::assertEquals(
            new Update(
                topics: ['http://localhost/admin/reviews/'.$id],
                data: json_encode(['@id' => 'http://localhost/admin/reviews/'.$id])
            ),
            self::getMercureMessage()
        );
        self::assertEquals(
            new Update(
                topics: ['http://localhost/books/'.$bookId.'/reviews/'.$id],
                data: json_encode(['@id' => 'http://localhost/books/'.$bookId.'/reviews/'.$id])
            ),
            self::getMercureMessage(1)
        );
    }
}
