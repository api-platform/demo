<?php

declare(strict_types=1);

namespace App\Security\Voter;

use ApiPlatform\Metadata\IriConverterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Check user permissions.
 *
 * @see https://www.keycloak.org/docs/latest/authorization_services/index.html#_service_obtaining_permissions
 */
final class OidcTokenPermissionVoter extends OidcVoter
{
    public function __construct(
        RequestStack $requestStack,
        #[Autowire('@security.access_token_extractor.header')]
        AccessTokenExtractorInterface $accessTokenExtractor,
        #[Autowire('%env(OIDC_API_CLIENT_ID)%')]
        private readonly string $oidcClientId,
        private readonly HttpClientInterface $securityAuthorizationClient,
        private readonly IriConverterInterface $iriConverter,
        private ?LoggerInterface $logger = null,
    ) {
        parent::__construct($requestStack, $accessTokenExtractor);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // todo find a feature requiring this voter
        return str_starts_with($attribute, 'OIDC_') && !empty($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (\is_object($subject)) {
            $subject = $this->iriConverter->getIriFromResource($subject);
        }

        if (!\is_string($subject)) {
            throw new \InvalidArgumentException(sprintf('Invalid subject type, expected "string" or "object", got "%s".', get_debug_type($subject)));
        }

        // ensure user is authenticated
        if (!$token->getUser() instanceof UserInterface) {
            return false;
        }

        $accessToken = $this->getToken();
        if (!$accessToken) {
            return false;
        }

        try {
            $response = $this->securityAuthorizationClient->request('POST', 'protocol/openid-connect/token', [
                'auth_bearer' => $accessToken,
                'body' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:uma-ticket',
                    'audience' => $this->oidcClientId,
                    'response_mode' => 'decision',
                    'permission_resource_format' => 'uri',
                    'permission_resource_matching_uri' => true,
                    'permission' => sprintf('%s', $subject),
                ],
            ]);

            return $response->toArray()['result'] ?? false;
        } catch (HttpExceptionInterface) {
            // OIDC server said no!
        } catch (ExceptionInterface $e) {
            $this->logger?->error('An error occurred while checking the permissions on OIDC server.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return false;
    }
}
