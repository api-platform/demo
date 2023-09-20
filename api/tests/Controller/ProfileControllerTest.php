<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Controller\ProfileController;
use App\DataFixtures\Story\DefaultUsersStory;
use App\Security\OidcTokenGenerator;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @see ProfileController
 */
final class ProfileControllerTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    private Client $client;

    protected function setup(): void
    {
        $this->client = self::createClient();
    }

    /**
     * @see ProfileController::__invoke()
     */
    public function testProfile(): void
    {
        DefaultUsersStory::load();

        $token = static::getContainer()->get(OidcTokenGenerator::class)->generate([
            'sub' => Uuid::v4()->__toString(),
            'email' => 'admin@example.com',
        ]);
        $this->client->request('GET', '/profile', [
            'auth_bearer' => $token,
        ]);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertJsonContains([
            'email' => 'admin@example.com',
            'roles' => ['ROLE_ADMIN'],
        ]);
    }

    /**
     * Custom claim "email" is missing.
     */
    public function testCannotGetProfileWithInvalidToken(): void
    {
        $token = static::getContainer()->get(OidcTokenGenerator::class)->generate([
            'sub' => Uuid::v4()->__toString(),
        ]);
        $this->client->request('GET', '/profile', [
            'auth_bearer' => $token,
        ]);
        self::assertResponseStatusCodeSame(401);
    }
}
