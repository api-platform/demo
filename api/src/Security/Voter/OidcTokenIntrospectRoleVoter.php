<?php

declare(strict_types=1);

namespace App\Security\Voter;

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
 * Check user roles from OIDC server.
 *
 * @see https://www.keycloak.org/docs/latest/authorization_services/index.html#obtaining-information-about-an-rpt
 */
final class OidcTokenIntrospectRoleVoter extends OidcVoter
{
    public function __construct(
        RequestStack $requestStack,
        #[Autowire('@security.access_token_extractor.header')]
        AccessTokenExtractorInterface $accessTokenExtractor,
        #[Autowire('%env(OIDC_API_CLIENT_ID)%')]
        private readonly string $oidcClientId,
        #[Autowire('%env(OIDC_API_CLIENT_SECRET)%')]
        private readonly string $oidcClientSecret,
        private readonly HttpClientInterface $securityAuthorizationClient,
        private ?LoggerInterface $logger = null,
    ) {
        parent::__construct($requestStack, $accessTokenExtractor);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return str_starts_with($attribute, 'OIDC_INTROSPECT_') && empty($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!empty($subject)) {
            throw new \InvalidArgumentException(sprintf('Invalid subject type, expected empty string or "null", got "%s".', get_debug_type($subject)));
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
            $response = $this->securityAuthorizationClient->request('POST', 'protocol/openid-connect/token/introspect', [
                'body' => [
                    'client_id' => $this->oidcClientId,
                    'client_secret' => $this->oidcClientSecret,
                    'token' => $accessToken,
                ],
            ]);

            $roles = array_map(static fn (string $role): string => strtolower($role), $response->toArray()['realm_access']['roles'] ?? []);

            return \in_array(strtolower(substr($attribute, 5)), $roles, true);
        } catch (HttpExceptionInterface) {
            // OIDC server said no!
        } catch (ExceptionInterface $e) {
            $this->logger?->error('An error occurred while checking the roles on OIDC server.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return false;
    }
}
