<?php

declare(strict_types=1);

namespace App\Tests\Api\Admin;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\DownloadFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Security\OidcTokenGenerator;
use App\Tests\Api\Admin\Trait\UsersDataProviderTrait;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class DownloadTest extends ApiTestCase
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
    public function testAsNonAdminUserICannotGetACollectionOfDownloads(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        DownloadFactory::createMany(10, ['user' => UserFactory::createOne()]);

        $options = [];
        if ($userFactory) {
            $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
                'sub' => Uuid::v4()->__toString(),
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/downloads', $options);

        self::assertResponseStatusCodeSame($expectedCode);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $hydraDescription,
        ]);
    }

    public function testAsAdminUserICanGetACollectionOfDownloads(): void
    {
        DownloadFactory::createMany(100, ['user' => UserFactory::createOne()]);

        $token = self::getContainer()->get(OidcTokenGenerator::class)->generate([
            'sub' => Uuid::v4()->__toString(),
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $response = $this->client->request('GET', '/admin/downloads', ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'hydra:totalItems' => 100,
        ]);
        self::assertCount(30, $response->toArray()['hydra:member']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/Download/collection.json'));
    }
}
