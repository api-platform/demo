<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\BookFactory;
use App\DataFixtures\Factory\BookmarkFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Security\OidcTokenGenerator;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class BookmarkTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    private Client $client;

    protected function setup(): void
    {
        $this->client = self::createClient();
    }

    public function testAsAnonymousICannotGetACollectionOfBookmarks(): void
    {
        BookmarkFactory::createMany(100);

        $this->client->request('GET', '/bookmarks');

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
     * Filters are disabled on /bookmarks.
     */
    public function testAsAUserICanGetACollectionOfMyBookmarksWithoutFilters(): void
    {
        BookmarkFactory::createMany(60);
        $user = UserFactory::createOne();
        BookmarkFactory::createMany(40, ['user' => $user]);

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => $user->email,
        ]);

        $response = $this->client->request('GET', '/bookmarks', ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'hydra:totalItems' => 40,
        ]);
        self::assertCount(30, $response->toArray()['hydra:member']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Bookmark/collection.json'));
    }

    public function testAsAnonymousICannotCreateABookmark(): void
    {
        $book = BookFactory::createOne(['book' => 'https://openlibrary.org/books/OL28346544M.json']);

        $this->client->request('POST', '/bookmarks', [
            'json' => [
                'book' => '/books/'.$book->getId(),
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

    public function testAsAUserICannotCreateABookmarkWithInvalidData(): void
    {
        $this->markTestIncomplete('Identifier "id" could not be transformed.');
        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => UserFactory::createOne()->email,
        ]);

        $this->client->request('POST', '/bookmarks', [
            'json' => [
                'book' => '/books/invalid',
            ],
            'auth_bearer' => $token,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'violations' => [
                [
                    'propertyPath' => 'book',
                    'message' => 'This value is not valid.',
                ],
            ],
        ]);
    }

    public function testAsAUserICanCreateABookmark(): void
    {
        $book = BookFactory::createOne(['book' => 'https://openlibrary.org/books/OL28346544M.json']);
        $user = UserFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'email' => $user->email,
        ]);

        $this->client->request('POST', '/bookmarks', [
            'json' => [
                'book' => '/books/'.$book->getId(),
            ],
            'auth_bearer' => $token,
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'book' => [
                '@id' => '/books/'.$book->getId(),
            ],
        ]);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Bookmark/item.json'));
    }
}
