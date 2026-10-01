<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Test\ApiTestCase;
use ApiPlatform\Test\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class OAuthProtectedResourceTest extends ApiTestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    #[Test]
    #[DataProvider(methodName: 'getUrls')]
    public function asAnMcpClientICanDiscoverTheAuthorizationServer(string $url): void
    {
        $this->client->request('GET', $url, [
            'headers' => [
                'Origin' => 'http://localhost:6274',
                // the metadata must stay reachable when the client holds an invalid token
                'Authorization' => 'Bearer invalid',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('access-control-allow-origin', 'http://localhost:6274');
        self::assertJsonEquals([
            'resource' => 'http://localhost/mcp',
            'authorization_servers' => [$_SERVER['OIDC_SERVER_URL']],
            'scopes_supported' => ['openid', 'profile', 'email'],
            'bearer_methods_supported' => ['header'],
        ]);
    }

    public static function getUrls(): iterable
    {
        yield 'resource path' => ['/.well-known/oauth-protected-resource/mcp'];
        yield 'root' => ['/.well-known/oauth-protected-resource'];
    }

    #[Test]
    public function asAnMcpClientIGetANotFoundOnTheResourceServerAuthorizationServerMetadata(): void
    {
        // MCP clients try this URL first, then fall back to the Keycloak one on a 4xx (a 5xx stops the discovery)
        $this->client->request('GET', '/.well-known/oauth-authorization-server/oidc/realms/demo');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
