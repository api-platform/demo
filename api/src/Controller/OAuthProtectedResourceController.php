<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * OAuth 2.0 Protected Resource Metadata (RFC 9728) of the MCP server: tells MCP clients which
 * authorization server issues the tokens it accepts.
 *
 * @see https://modelcontextprotocol.io/specification/2025-11-25/basic/authorization
 */
#[AsController]
final readonly class OAuthProtectedResourceController
{
    public function __construct(
        #[Autowire(env: 'OIDC_SERVER_URL')]
        private string $authorizationServer,
    ) {
    }

    // RFC 9728 inserts the resource path after the well-known prefix, some clients only try the root
    #[Route('/.well-known/oauth-protected-resource/mcp', name: 'oauth_protected_resource_mcp', methods: ['GET'])]
    #[Route('/.well-known/oauth-protected-resource', name: 'oauth_protected_resource', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse([
            'resource' => $request->getSchemeAndHttpHost() . '/mcp',
            'authorization_servers' => [$this->authorizationServer],
            'scopes_supported' => ['openid', 'profile', 'email'],
            'bearer_methods_supported' => ['header'],
        ]);
    }
}
