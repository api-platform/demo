<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Foundry\Test\ResetDatabase;

class RootTest extends ApiTestCase
{
    use ResetDatabase;

    private Client $client;

    protected function setup(): void
    {
        $this->client = self::createClient();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     *
     * @test
     */
    public function optionsWhenNotLoggedIn(): void
    {
        $this->client->request(
            'OPTIONS',
            '/',
            [
                'headers' => [
                    'Origin' => 'http://localhost:3000',
                    'Access-Control-Request-Method' => 'GET',
                    'Access-Control-Request-Headers' => 'Origin, Content-Type, Accept, Authorization',
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
    }
}
