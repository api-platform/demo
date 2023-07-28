<?php

declare(strict_types=1);

namespace App\Tests\Api\Admin;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\BookFactory;
use App\DataFixtures\Factory\ReviewFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\Book;
use App\Entity\User;
use App\Repository\ReviewRepository;
use App\Security\OidcTokenGenerator;
use App\Tests\Api\Admin\Trait\UsersDataProviderTrait;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ReviewTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;
    use UsersDataProviderTrait;

    private Client $client;

    protected function setup(): void
    {
        $this->client = self::createClient();
    }

    /**
     * @dataProvider getNonAdminUsers
     */
    public function testAsNonAdminUserICannotGetACollectionOfReviews(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $options = [];
        if ($userFactory) {
            $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/reviews', $options);

        self::assertResponseStatusCodeSame($expectedCode);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $hydraDescription,
        ]);
    }

    /**
     * @dataProvider getAdminUrls
     */
    public function testAsAdminUserICanGetACollectionOfReviews(FactoryCollection $factory, string|callable $url, int $hydraTotalItems): void
    {
        $factory->create();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        if (is_callable($url)) {
            $url = $url();
        }

        $response = $this->client->request('GET', $url, ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'hydra:totalItems' => $hydraTotalItems,
        ]);
        self::assertCount(min($hydraTotalItems, 30), $response->toArray()['hydra:member']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Review/collection.json'));
    }

    public function getAdminUrls(): iterable
    {
        yield 'all reviews' => [
            ReviewFactory::new()->many(100),
            '/admin/reviews',
            100,
        ];
        yield 'all reviews using itemsPerPage' => [
            ReviewFactory::new()->many(100),
            '/admin/reviews?itemsPerPage=10',
            100,
        ];
        yield 'reviews filtered by rating' => [
            ReviewFactory::new()->sequence(function () {
                foreach (range(1, 100) as $i) {
                    // 33% of reviews are rated 5
                    yield ['rating' => $i % 3 ? 3 : 5];
                }
            }),
            '/admin/reviews?rating=5',
            33,
        ];
        yield 'reviews filtered by user' => [
            ReviewFactory::new()->sequence(function () {
                $user = UserFactory::createOne(['email' => 'user@example.com']);
                yield ['user' => $user];
                foreach (range(1, 10) as $i) {
                    yield ['user' => UserFactory::createOne()];
                }
            }),
            static function (): string {
                /** @var User[] $users */
                $users = UserFactory::findBy(['email' => 'user@example.com']);

                return '/admin/reviews?user=/admin/users/'.$users[0]->getId();
            },
            1,
        ];
        yield 'reviews filtered by book' => [
            ReviewFactory::new()->sequence(function () {
                yield ['book' => BookFactory::createOne(['title' => 'Foundation'])];
                foreach (range(1, 10) as $i) {
                    yield ['book' => BookFactory::createOne()];
                }
            }),
            static function (): string {
                /** @var Book[] $books */
                $books = BookFactory::findBy(['title' => 'Foundation']);

                return '/admin/reviews?book=/books/'.$books[0]->getId();
            },
            1,
        ];
    }

    /**
     * @dataProvider getNonAdminUsers
     */
    public function testAsNonAdminUserICannotGetAReview(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $review = ReviewFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/reviews/'.$review->getId(), $options);

        self::assertResponseStatusCodeSame($expectedCode);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $hydraDescription,
        ]);
    }

    public function testAsAdminUserICannotGetAnInvalidReview(): void
    {
        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('GET', '/admin/reviews/invalid', ['auth_bearer' => $token]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAsAdminUserICanGetAReview(): void
    {
        $review = ReviewFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('GET', '/admin/reviews/'.$review->getId(), ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Review/item.json'));
    }

    /**
     * @dataProvider getNonAdminUsers
     */
    public function testAsNonAdminUserICannotUpdateAReview(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $review = ReviewFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/reviews/'.$review->getId(), $options + [
            'json' => [
                'body' => 'Very good book!',
                'rating' => 5,
            ],
        ]);

        self::assertResponseStatusCodeSame($expectedCode);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $hydraDescription,
        ]);
    }

    public function testAsAdminUserICannotUpdateAnInvalidReview(): void
    {
        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('PATCH', '/admin/reviews/invalid', [
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

    public function testAsAdminUserICanUpdateAReview(): void
    {
        $review = ReviewFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('PATCH', '/admin/reviews/'.$review->getId(), [
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

    /**
     * @dataProvider getNonAdminUsers
     */
    public function testAsNonAdminUserICannotDeleteAReview(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $review = ReviewFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('DELETE', '/admin/reviews/'.$review->getId(), $options);

        self::assertResponseStatusCodeSame($expectedCode);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $hydraDescription,
        ]);
    }

    public function testAsAdminUserICannotDeleteAnInvalidReview(): void
    {
        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('DELETE', '/admin/reviews/invalid', ['auth_bearer' => $token]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAsAdminUserICanDeleteAReview(): void
    {
        $review = ReviewFactory::createOne()->disableAutoRefresh();
        $id = $review->getId();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('DELETE', '/admin/reviews/'.$review->getId(), ['auth_bearer' => $token]);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertNull(self::getContainer()->get(ReviewRepository::class)->find($id));
    }
}
