<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\BookFactory;
use App\DataFixtures\Factory\DownloadFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Security\OidcTokenGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class DownloadTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    private Client $client;

    protected function setup(): void
    {
        $this->client = self::createClient();
    }

    public function testAsAnonymousICannotGetACollectionOfDownloads(): void
    {
        DownloadFactory::createMany(100);

        $this->client->request('GET', '/downloads');

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
     * Filters are disabled on /downloads.
     */
    public function testAsAUserICanGetACollectionOfMyDownloadsWithoutFilters(): void
    {
        DownloadFactory::createMany(60);
        $user = UserFactory::createOne();
        DownloadFactory::createMany(40, ['user' => $user]);

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'sub' => Uuid::v4()->__toString(),
            'email' => $user->email,
        ]);

        $response = $this->client->request('GET', '/downloads', ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'hydra:totalItems' => 40,
        ]);
        self::assertCount(30, $response->toArray()['hydra:member']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Download/collection.json'));
    }

    public function testAsAnonymousICannotCreateADownload(): void
    {
        $book = BookFactory::createOne();

        $this->client->request('POST', '/downloads', [
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

    public function testAsAUserICannotCreateADownloadWithInvalidData(): void
    {
        $this->markTestIncomplete('Identifier "id" could not be transformed.');
        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'sub' => Uuid::v4()->__toString(),
            'email' => UserFactory::createOne()->email,
        ]);

        $this->client->request('POST', '/downloads', [
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

    public function testAsAUserICanCreateADownload(): void
    {
        $book = BookFactory::createOne();
        $user = UserFactory::createOne();

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'sub' => Uuid::v4()->__toString(),
            'email' => $user->email,
        ]);

        $this->client->request('POST', '/downloads', [
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
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Download/item.json'));
    }
}
