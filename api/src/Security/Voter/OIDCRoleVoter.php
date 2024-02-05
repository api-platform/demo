<?php

namespace App\Security\Voter;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Check user roles.
 *
 * @see https://www.keycloak.org/docs/latest/authorization_services/index.html#obtaining-information-about-an-rpt
 */
final class OIDCRoleVoter extends Voter
{
    use OIDCVoterTrait;

    public function __construct(
        #[Autowire('%env(OIDC_API_CLIENT_ID)%')]
        private readonly string $oidcClientId,
        #[Autowire('%env(OIDC_API_CLIENT_SECRET)%')]
        private readonly string $oidcClientSecret,
        private readonly HttpClientInterface $securityAuthorizationClient,
        private readonly RequestStack $requestStack,
        #[Autowire('@security.access_token_extractor.header')]
        private readonly AccessTokenExtractorInterface $accessTokenExtractor,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return empty($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $accessToken = $this->getToken($token);
        if (!$accessToken) {
            return false;
        }

        if (!empty($subject)) {
            throw new \InvalidArgumentException(sprintf('Invalid subject type, expected empty string or "null", got "%s".', get_debug_type($subject)));
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

            return in_array(strtolower($attribute), $roles, true);
        } catch (ExceptionInterface) {
            return false;
        }
    }
}
