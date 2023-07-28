<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\BookFactory;
use App\DataFixtures\Factory\ReviewFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Book;
use App\Entity\User;
use App\Repository\ReviewRepository;
use App\Security\OidcTokenGenerator;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ReviewTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

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
                'book' => '/books/'.$book->getId(),
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
    public function testAsAUserICannotAddAReviewOnABookWithInvalidData(array $data, array $violations): void
    {
        $book = BookFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOne()->email,
        ]);

        $this->client->request('POST', '/books/'.$book->getId().'/reviews', [
            'auth_bearer' => $token,
            'json' => $data,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => $violations,
        ]);
    }

    public function getInvalidData(): iterable
    {
        yield [
            [],
            [
                [
                    'propertyPath' => 'book',
                    'message' => 'This value should not be null.',
                ],
                [
                    'propertyPath' => 'body',
                    'message' => 'This value should not be blank.',
                ],
                [
                    'propertyPath' => 'rating',
                    'message' => 'This value should not be null.',
                ],
            ],
        ];
        //        yield [
        //            [
        //                'book' => 'invalid book',
        //                'body' => 'Very good book!',
        //                'rating' => 5,
        //            ],
        //            [
        //                [
        //                    'propertyPath' => 'book',
        //                    'message' => 'This value is not a valid URL.',
        //                ],
        //            ]
        //        ];
    }

    public function testAsAUserICanAddAReviewOnABook(): void
    {
        $book = BookFactory::createOne();
        $user = UserFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => $user->email,
        ]);

        $this->client->request('POST', '/books/'.$book->getId().'/reviews', [
            'auth_bearer' => $token,
            'json' => [
                'book' => '/books/'.$book->getId(),
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
    }

    public function testAsAnonymousICannotGetAnInvalidReview(): void
    {
        $book = BookFactory::createOne();

        $this->client->request('GET', '/books/'.$book->getId().'/reviews/invalid');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAsAnonymousICanGetABookReview(): void
    {
        $review = ReviewFactory::createOne();

        $this->client->request('GET', '/books/'.$review->book->getId().'/reviews/'.$review->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Review/item.json'));
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

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
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

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
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

    public function testAsAUserICanUpdateMyBookReview(): void
    {
        $review = ReviewFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
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

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
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

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOne()->email,
        ]);

        $this->client->request('PATCH', '/books/'.$book->getId().'/reviews/invalid', [
            'auth_bearer' => $token,
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAsAUserICanDeleteMyBookReview(): void
    {
        $review = ReviewFactory::createOne()->disableAutoRefresh();
        $id = $review->getId();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => $review->user->email,
        ]);

        $this->client->request('DELETE', '/books/'.$review->book->getId().'/reviews/'.$review->getId(), [
            'auth_bearer' => $token,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertNull(self::getContainer()->get(ReviewRepository::class)->find($id));
    }
}
