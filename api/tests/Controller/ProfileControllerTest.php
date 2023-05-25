<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Controller\ProfileController;
use App\DataFixtures\Story\DefaultUsersStory;
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

        $response = $this->client->request('POST', '/login', [
            'json' => [
                'username' => 'admin@example.com',
                'password' => 'admin',
            ],
        ]);
        $this->client->request('GET', '/profile', [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $response->toArray()['token']),
            ],
        ]);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertJsonContains([
            'email' => 'admin@example.com',
            'roles' => ['ROLE_ADMIN'],
        ]);
    }
}
