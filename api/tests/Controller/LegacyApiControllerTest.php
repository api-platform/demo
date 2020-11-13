<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Controller\LegacyApiController;

/**
 * @see LegacyApiController
 */
class LegacyApiControllerTest extends ApiTestCase
{
    private Client $client;

    protected function setup(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @see LegacyApiController::stats()
     */
    public function testStats(): void
    {
        $this->client->request('GET', '/stats');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertJsonEquals([
            'books_count' => 1000,
            'topbooks_count' => 100,
        ]);
    }
}
