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
     */
    public function testAsNonAdminUserICannotGetAUser(int $expectedCode, string $hydraDescription, ?UserFactory $userFactory): void
    {
        $user = UserFactory::createOne();

        $options = [];
        if ($userFactory) {
            $token = $this->generateToken([
                'email' => $userFactory->create()->email,
            ]);
            $options['auth_bearer'] = $token;
        }

        $this->client->request('GET', '/admin/users/'.$user->getId(), $options);

        self::assertResponseStatusCodeSame($expectedCode);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $hydraDescription,
        ]);
    }

    public function testAsAdminUserICanGetAUser(): void
    {
        $user = UserFactory::createOne();

        $token = $this->generateToken([
            'email' => UserFactory::createOneAdmin()->email,
        ]);

        $this->client->request('GET', '/admin/users/'.$user->getId(), ['auth_bearer' => $token]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@id' => '/admin/users/'.$user->getId(),
        ]);
        // note: email property is never exposed
        self::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/User/item.json'));
    }

    public function testAsAUserIAmUpdatedOnLogin(): void
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
