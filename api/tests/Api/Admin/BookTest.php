<?php

declare(strict_types=1);

namespace App\Tests\Api\Admin;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\BookFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Enum\BookCondition;
use App\Security\OidcTokenGenerator;
use App\Tests\Api\Admin\Trait\UsersDataProviderTrait;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class BookTest extends ApiTestCase
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
    public function testAsNonAdminUserICannotGetACollectionOfBooks(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $options = [];
        if ($userFactory) {
            $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/books', $options);

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
     * @dataProvider getUrls
     */
    public function testAsAdminUserICanGetACollectionOfBooks(FactoryCollection $factory, string $url, int $hydraTotalItems): void
    {
        $factory->create();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $response = $this->client->request('GET', $url, ['auth_bearer' => $token]);

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
            '/admin/books',
            100,
        ];
        yield 'books filtered by title' => [
            BookFactory::new()->sequence(function () {
                yield ['title' => 'Foundation'];
                foreach (range(1, 100) as $i) {
                    yield [];
                }
            }),
            '/admin/books?title=ounda',
            1,
        ];
        yield 'books filtered by author' => [
            BookFactory::new()->sequence(function () {
                yield ['author' => 'Isaac Asimov'];
                foreach (range(1, 100) as $i) {
                    yield [];
                }
            }),
            '/admin/books?author=isaac',
            1,
        ];
        yield 'books filtered by condition' => [
            BookFactory::new()->sequence(function () {
                foreach (range(1, 100) as $i) {
                    // 33% of books are damaged
                    yield ['condition' => $i % 3 ? BookCondition::NewCondition : BookCondition::DamagedCondition];
                }
            }),
            '/admin/books?condition='.BookCondition::DamagedCondition->value,
            33,
        ];
    }

    public function testAsAdminUserICanGetACollectionOfBooksOrderedByTitle(): void
    {
        BookFactory::createOne(['title' => 'Foundation']);
        BookFactory::createOne(['title' => 'Nemesis']);
        BookFactory::createOne(['title' => 'I, Robot']);

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $response = $this->client->request('GET', '/admin/books?order[title]=asc', ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertEquals('Foundation', $response->toArray()['hydra:member'][0]['title']);
        self::assertEquals('I, Robot', $response->toArray()['hydra:member'][1]['title']);
        self::assertEquals('Nemesis', $response->toArray()['hydra:member'][2]['title']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Book/collection.json'));
    }

    /**
     * @dataProvider getAllUsers
     */
    public function testAsAnyUserICannotGetAnInvalidBook(?UserFactory $userFactory): void
    {
        BookFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/books/invalid', $options);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function getAllUsers(): iterable
    {
        yield [null];
        yield [UserFactory::new()];
        yield [UserFactory::new(['roles' => ['ROLE_ADMIN']])];
    }

    /**
     * @dataProvider getNonAdminUsers
     */
    public function testAsNonAdminUserICannotGetABook(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $book = BookFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/books/'.$book->getId(), $options);

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
     * @dataProvider getNonAdminUsers
     */
    public function testAsAdminUserICanGetABook(): void
    {
        $book = BookFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('GET', '/admin/books/'.$book->getId(), ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@id' => '/admin/books/'.$book->getId(),
            'book' => $book->book,
            'condition' => $book->condition->value,
            'title' => $book->title,
            'author' => $book->author,
        ]);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Book/item.json'));
    }

    /**
     * @dataProvider getNonAdminUsers
     */
    public function testAsNonAdminUserICannotCreateABook(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $options = [];
        if ($userFactory) {
            $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('POST', '/admin/books', $options + [
            'json' => [
                'book' => 'https://openlibrary.org/books/OL28346544M.json',
                'condition' => BookCondition::NewCondition->value,
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

    /**
     * @dataProvider getInvalidData
     */
    public function testAsAdminUserICannotCreateABookWithInvalidData(array $data, array $violations): void
    {
        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('POST', '/admin/books', [
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
                    'message' => 'This value should not be blank.',
                ],
                [
                    'propertyPath' => 'condition',
                    'message' => 'This value should not be null.',
                ],
            ],
        ];
        yield [
            [
                'book' => 'invalid book',
                'condition' => BookCondition::NewCondition->value,
            ],
            [
                [
                    'propertyPath' => 'book',
                    'message' => 'This value is not a valid URL.',
                ],
            ],
        ];
    }

    /**
     * @group apiCall
     */
    public function testAsAdminUserICanCreateABook(): void
    {
        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('POST', '/admin/books', [
            'auth_bearer' => $token,
            'json' => [
                'book' => 'https://openlibrary.org/books/OL28346544M.json',
                'condition' => BookCondition::NewCondition->value,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'book' => 'https://openlibrary.org/books/OL28346544M.json',
            'condition' => BookCondition::NewCondition->value,
        ]);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Book/item.json'));
    }

    /**
     * @dataProvider getNonAdminUsers
     */
    public function testAsNonAdminUserICannotUpdateBook(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $book = BookFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('PUT', '/admin/books/'.$book->getId(), $options + [
            'json' => [
                'book' => 'https://openlibrary.org/books/OL28346544M.json',
                'condition' => BookCondition::NewCondition->value,
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

    public function testAsAdminUserICannotUpdateAnInvalidBook(): void
    {
        BookFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('PUT', '/admin/books/invalid', [
            'auth_bearer' => $token,
            'json' => [
                'condition' => BookCondition::DamagedCondition->value,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testAsAdminUserICannotUpdateABookWithInvalidData(array $data, array $violations): void
    {
        BookFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('PUT', '/admin/books/invalid', [
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

    /**
     * @group apiCall
     */
    public function testAsAdminUserICanUpdateABook(): void
    {
        $book = BookFactory::createOne([
            'book' => 'https://openlibrary.org/books/OL28346544M.json',
        ]);

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('PUT', '/admin/books/'.$book->getId(), [
            'auth_bearer' => $token,
            'json' => [
                'condition' => BookCondition::DamagedCondition->value,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'condition' => BookCondition::DamagedCondition->value,
        ]);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Book/item.json'));
    }

    /**
     * @dataProvider getNonAdminUsers
     */
    public function testAsNonAdminUserICannotDeleteABook(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $book = BookFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('DELETE', '/admin/books/'.$book->getId(), $options);

        self::assertResponseStatusCodeSame($expectedCode);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $hydraDescription,
        ]);
    }

    public function testAsAdminUserICannotDeleteAnInvalidBook(): void
    {
        BookFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('DELETE', '/admin/books/invalid', ['auth_bearer' => $token]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAsAdminUserICanDeleteABook(): void
    {
        $book = BookFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $response = $this->client->request('DELETE', '/admin/books/'.$book->getId(), ['auth_bearer' => $token]);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertEmpty($response->getContent());
    }
}
