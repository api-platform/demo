<?php

declare(strict_types=1);

namespace App\Tests\Api\Admin;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\BookFactory;
use App\DataFixtures\Factory\ReviewFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Book;
use App\Entity\Review;
use App\Entity\User;
use App\Tests\Api\Admin\Trait\UsersDataProviderTrait;
use App\Tests\Api\Trait\SecurityTrait;
use App\Tests\Api\Trait\SerializerTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Update;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ReviewTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;
    use SecurityTrait;
    use SerializerTrait;
    use UsersDataProviderTrait;

    private Client $client;

    protected function setup(): void
    {
        $this->client = self::createClient();
    }

    /**
     * @dataProvider getNonAdminUsers
     *
     * @test
     */
    public function asNonAdminUserICannotGetACollectionOfReviews(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $options = [];
        if ($userFactory) {
            $token = $this->generateToken([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/reviews', $options);

        self::assertResponseStatusCodeSame($expectedCode);
        self::assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');
        self::assertResponseHeaderSame('link', '<http://www.w3.org/ns/hydra/error>; rel="http://www.w3.org/ns/json-ld#error",<http://localhost/docs.jsonld>; rel="http://www.w3.org/ns/hydra/core#apiDocumentation"');
        self::assertJsonContains([
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $hydraDescription,
        ]);
    }

    /**
     * @dataProvider getAdminUrls
     *
     * @test
     */
    public function asAdminUserICanGetACollectionOfReviews(FactoryCollection $factory, callable|string $url, int $hydraTotalItems, int $itemsPerPage = null): void
    {
        $factory->create();

        $token = $this->generateToken([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        if (\is_callable($url)) {
            $url = $url();
        }

        $response = $this->client->request('GET', $url, ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'hydra:totalItems' => $hydraTotalItems,
        ]);
        self::assertCount(min($itemsPerPage ?? $hydraTotalItems, 30), $response->toArray()['hydra:member']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/Review/collection.json'));
    }

    public static function getAdminUrls(): iterable
    {
        yield 'all reviews' => [
            ReviewFactory::new()->many(35),
            '/admin/reviews',
            35,
        ];
        yield 'all reviews using itemsPerPage' => [
            ReviewFactory::new()->many(35),
            '/admin/reviews?itemsPerPage=10',
            35,
            10,
        ];
        yield 'reviews filtered by rating' => [
            ReviewFactory::new()->sequence(static function () {
                foreach (range(1, 100) as $i) {
                    // 33% of reviews are rated 5
                    yield ['rating' => $i % 3 ? 3 : 5];
                }
            }),
            '/admin/reviews?rating=5',
            33,
        ];
        yield 'reviews filtered by user' => [
            ReviewFactory::new()->sequence(static function () {
                $user = UserFactory::createOne(['email' => 'user@example.com']);
                yield ['user' => $user];
                foreach (range(1, 10) as $i) {
                    yield ['user' => UserFactory::createOne()];
                }
            }),
            static function (): string {
                /** @var User[] $users */
                $users = UserFactory::findBy(['email' => 'user@example.com']);

                return '/admin/reviews?user=/admin/users/' . $users[0]->getId();
            },
            1,
        ];
        yield 'reviews filtered by book' => [
            ReviewFactory::new()->sequence(static function () {
                yield ['book' => BookFactory::createOne(['title' => 'Hyperion'])];
                foreach (range(1, 10) as $i) {
                    yield ['book' => BookFactory::createOne()];
                }
            }),
            static function (): string {
                /** @var Book[] $books */
                $books = BookFactory::findBy(['title' => 'Hyperion']);

                return '/admin/reviews?book=/books/' . $books[0]->getId();
            },
            1,
        ];
    }

    /**
     * @dataProvider getNonAdminUsers
     *
     * @test
     */
    public function asNonAdminUserICannotGetAReview(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $review = ReviewFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = $this->generateToken([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/reviews/' . $review->getId(), $options);

        self::assertResponseStatusCodeSame($expectedCode);
        self::assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');
        self::assertResponseHeaderSame('link', '<http://www.w3.org/ns/hydra/error>; rel="http://www.w3.org/ns/json-ld#error",<http://localhost/docs.jsonld>; rel="http://www.w3.org/ns/hydra/core#apiDocumentation"');
        self::assertJsonContains([
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $hydraDescription,
        ]);
    }

    /**
     * @test
     */
    public function asAdminUserICannotGetAnInvalidReview(): void
    {
        $token = $this->generateToken([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('GET', '/admin/reviews/invalid', ['auth_bearer' => $token]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function asAdminUserICanGetAReview(): void
    {
        $review = ReviewFactory::createOne();

        $token = $this->generateToken([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('GET', '/admin/reviews/' . $review->getId(), ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/Review/item.json'));
    }

    /**
     * @dataProvider getNonAdminUsers
     *
     * @test
     */
    public function asNonAdminUserICannotUpdateAReview(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $review = ReviewFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = $this->generateToken([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/reviews/' . $review->getId(), $options + [
            'json' => [
                'body' => 'Very good book!',
                'rating' => 5,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
        ]);

        self::assertResponseStatusCodeSame($expectedCode);
        self::assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');
        self::assertResponseHeaderSame('link', '<http://www.w3.org/ns/hydra/error>; rel="http://www.w3.org/ns/json-ld#error",<http://localhost/docs.jsonld>; rel="http://www.w3.org/ns/hydra/core#apiDocumentation"');
        self::assertJsonContains([
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $hydraDescription,
        ]);
    }

    /**
     * @test
     */
    public function asAdminUserICannotUpdateAnInvalidReview(): void
    {
        $token = $this->generateToken([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('PUT', '/admin/reviews/invalid', [
            'auth_bearer' => $token,
            'json' => [
                'body' => 'Very good book!',
                'rating' => 5,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @group mercure
     *
     * @test
     */
    public function asAdminUserICanUpdateAReview(): void
    {
        $book = BookFactory::createOne();
        $review = ReviewFactory::createOne(['book' => $book]);
        $user = UserFactory::createOneAdmin();

        $token = $this->generateToken([
            'email' => $user->email,
        ]);

        $this->client->request('PUT', '/admin/reviews/' . $review->getId(), [
            'auth_bearer' => $token,
            'json' => [
                // Must set all data because of standard PUT
                'book' => '/admin/books/' . $book->getId(),
                'letter' => null,
                'body' => 'Very good book!',
                'rating' => 5,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'body' => 'Very good book!',
            'rating' => 5,
        ]);
        // ensure user hasn't changed
        self::assertNotEquals($user, $review->object()->user);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/Review/item.json'));
        self::assertCount(2, self::getMercureMessages());
        self::assertEquals(
            new Update(
                topics: ['http://localhost/admin/reviews/' . $review->getId()],
                data: self::serialize(
                    $review->object(),
                    'jsonld',
                    self::getOperationNormalizationContext(Review::class, '/admin/reviews/{id}{._format}')
                ),
            ),
            self::getMercureMessage()
        );
        self::assertEquals(
            new Update(
                topics: ['http://localhost/books/' . $review->book->getId() . '/reviews/' . $review->getId()],
                data: self::serialize(
                    $review->object(),
                    'jsonld',
                    self::getOperationNormalizationContext(Review::class, '/books/{bookId}/reviews/{id}{._format}')
                ),
            ),
            self::getMercureMessage(1)
        );
    }

    /**
     * @dataProvider getNonAdminUsers
     *
     * @test
     */
    public function asNonAdminUserICannotDeleteAReview(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $review = ReviewFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = $this->generateToken([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('DELETE', '/admin/reviews/' . $review->getId(), $options);

        self::assertResponseStatusCodeSame($expectedCode);
        self::assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');
        self::assertResponseHeaderSame('link', '<http://www.w3.org/ns/hydra/error>; rel="http://www.w3.org/ns/json-ld#error",<http://localhost/docs.jsonld>; rel="http://www.w3.org/ns/hydra/core#apiDocumentation"');
        self::assertJsonContains([
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $hydraDescription,
        ]);
    }

    /**
     * @test
     */
    public function asAdminUserICannotDeleteAnInvalidReview(): void
    {
        $token = $this->generateToken([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('DELETE', '/admin/reviews/invalid', ['auth_bearer' => $token]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @group mercure
     *
     * @test
     */
    public function asAdminUserICanDeleteAReview(): void
    {
        $review = ReviewFactory::createOne(['body' => 'Best book ever!']);
        $id = $review->getId();
        $bookId = $review->book->getId();

        $token = $this->generateToken([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $response = $this->client->request('DELETE', '/admin/reviews/' . $review->getId(), [
            'auth_bearer' => $token,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertEmpty($response->getContent());
        ReviewFactory::assert()->notExists(['body' => 'Best book ever!']);
        self::assertCount(2, self::getMercureMessages());
        // todo how to ensure it's a delete update
        self::assertEquals(
            new Update(
                topics: ['http://localhost/admin/reviews/' . $id],
                data: json_encode(['@id' => 'http://localhost/admin/reviews/' . $id])
            ),
            self::getMercureMessage()
        );
        self::assertEquals(
            new Update(
                topics: ['http://localhost/books/' . $bookId . '/reviews/' . $id],
                data: json_encode(['@id' => 'http://localhost/books/' . $bookId . '/reviews/' . $id])
            ),
            self::getMercureMessage(1)
        );
    }
}
