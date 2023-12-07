<?php

declare(strict_types=1);

namespace App\Tests\Api\Admin;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Tests\Api\Admin\Trait\UsersDataProviderTrait;
use App\Tests\Api\Trait\SecurityTrait;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class UserTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;
    use SecurityTrait;
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
    public function asNonAdminUserICannotGetACollectionOfUsers(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $options = [];
        if ($userFactory) {
            $token = $this->generateToken([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/users', $options);

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
    public function asAdminUserICanGetACollectionOfUsers(FactoryCollection $factory, callable|string $url, int $hydraTotalItems, int $itemsPerPage = null): void
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
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/User/collection.json'));
    }

    public static function getAdminUrls(): iterable
    {
        yield 'all users' => [
            UserFactory::new()->many(34),
            '/admin/users',
            35,
        ];
        yield 'all users using itemsPerPage' => [
            UserFactory::new()->many(34),
            '/admin/users?itemsPerPage=10',
            35,
            10,
        ];
        yield 'users filtered by name' => [
            UserFactory::new()->sequence(static function () {
                yield ['firstName' => 'John', 'lastName' => 'DOE'];
                foreach (range(1, 10) as $i) {
                    yield [];
                }
            }),
            '/admin/users?name=John+DOE',
            1,
        ];
    }

    /**
     * @dataProvider getNonAdminUsers
     *
     * @test
     */
    public function asNonAdminUserICannotGetAUser(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $user = UserFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = $this->generateToken([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/users/' . $user->getId(), $options);

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
    public function asAdminUserICanGetAUser(): void
    {
        $user = UserFactory::createOne();

        $token = $this->generateToken([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('GET', '/admin/users/' . $user->getId(), ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@id' => '/admin/users/' . $user->getId(),
        ]);
        // note: email property is never exposed
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/User/item.json'));
    }

    /**
     * @test
     */
    public function asAUserIAmUpdatedOnLogin(): void
    {
        $user = UserFactory::createOne([
            'firstName' => 'John',
            'lastName' => 'DOE',
            'sub' => Uuid::fromString('b5c5bff1-5b5f-4a73-8fc8-4ea8f18586a9'),
        ])->disableAutoRefresh();

        $sub = Uuid::v7()->__toString();
        $token = $this->generateToken([
            'sub' => $sub,
            'email' => $user->email,
            'given_name' => 'Chuck',
            'family_name' => 'NORRIS',
            'name' => 'Chuck NORRIS',
        ]);

        $this->client->request('GET', '/books', ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        $user = self::getContainer()->get(UserRepository::class)->find($user->getId());
        self::assertNotNull($user);
        self::assertEquals('Chuck', $user->firstName);
        self::assertEquals('NORRIS', $user->lastName);
        self::assertEquals('Chuck NORRIS', $user->getName());
        self::assertEquals($sub, $user->sub);
    }
}
