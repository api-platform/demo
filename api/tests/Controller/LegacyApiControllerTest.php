<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Controller\LegacyApiController;

/**
 * @see LegacyApiController
 */
final class LegacyApiControllerTest extends ApiTestCase
{
    private Client $client;

    protected function setup(): void
    {
        $this->client = self::createClient();
    }

    /**
     * @see LegacyApiController::__invoke()
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
