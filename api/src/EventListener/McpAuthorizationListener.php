<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Operation\Factory\OperationMetadataFactoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Starts the MCP authorization flow (OAuth 2.1) for anonymous clients.
 *
 * API Platform answers a denied MCP tool call with a JSON-RPC error over HTTP 200, while MCP clients
 * only start the OAuth flow on an HTTP 401 pointing to the protected resource metadata (RFC 9728).
 *
 * @see https://modelcontextprotocol.io/specification/2025-11-25/basic/authorization#authorization-server-location
 */
final readonly class McpAuthorizationListener
{
    private const string PATH = '/mcp';

    public function __construct(
        #[Autowire(service: 'api_platform.mcp.metadata.operation.mcp_factory')]
        private OperationMetadataFactoryInterface $operationMetadataFactory,
        private Security $security,
    ) {
    }

    // after the firewall (priority 8), before the MCP controller
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 4)]
    public function challengeAnonymousToolCall(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || self::PATH !== $request->getPathInfo() || !$request->isMethod('POST') || $this->security->getUser() instanceof UserInterface) {
            return;
        }

        $message = json_decode($request->getContent(), true);
        if (!\is_array($message) || 'tools/call' !== ($message['method'] ?? null) || !\is_string($name = $message['params']['name'] ?? null)) {
            return;
        }

        // every secured tool of this demo requires an authenticated user
        $operation = $this->operationMetadataFactory->create($name);
        if (!$operation instanceof Operation || (null === $operation->getSecurity() && null === $operation->getSecurityPostDenormalize() && null === $operation->getSecurityPostValidation())) {
            return;
        }

        $event->setResponse(new Response(status: Response::HTTP_UNAUTHORIZED, headers: [
            'WWW-Authenticate' => \sprintf('Bearer resource_metadata="%s"', $this->getResourceMetadataUrl($request)),
        ]));
    }

    // invalid or expired tokens are rejected by the firewall: point the client to the metadata as well
    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function addResourceMetadataToChallenge(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (Response::HTTP_UNAUTHORIZED !== $response->getStatusCode() || !str_starts_with($event->getRequest()->getPathInfo(), self::PATH)) {
            return;
        }

        $challenge = $response->headers->get('WWW-Authenticate');
        if (null !== $challenge && str_contains($challenge, 'resource_metadata=')) {
            return;
        }

        $resourceMetadata = \sprintf('resource_metadata="%s"', $this->getResourceMetadataUrl($event->getRequest()));
        $response->headers->set('WWW-Authenticate', null !== $challenge ? $challenge . ', ' . $resourceMetadata : 'Bearer ' . $resourceMetadata);
    }

    private function getResourceMetadataUrl(Request $request): string
    {
        return $request->getSchemeAndHttpHost() . '/.well-known/oauth-protected-resource' . self::PATH;
    }
}
