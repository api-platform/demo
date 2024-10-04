<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;

/**
 * Check user roles from token.
 *
 * @see https://www.keycloak.org/docs/latest/authorization_services/index.html#obtaining-information-about-an-rpt
 */
final class OidcRoleVoter extends OidcVoter
{
    public function __construct(
        RequestStack $requestStack,
        #[Autowire('@security.access_token_extractor.header')]
        AccessTokenExtractorInterface $accessTokenExtractor,
        #[Autowire('@jose.jws_serializer.oidc')]
        private readonly JWSSerializerManager $jwsSerializerManager,
    ) {
        parent::__construct($requestStack, $accessTokenExtractor);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return str_starts_with($attribute, 'OIDC_') && empty($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!empty($subject)) {
            throw new \InvalidArgumentException(\sprintf('Invalid subject type, expected empty string or "null", got "%s".', get_debug_type($subject)));
        }

        // ensure user is authenticated
        if (!$token->getUser() instanceof UserInterface) {
            return false;
        }

        $accessToken = $this->getToken();
        if (!$accessToken) {
            return false;
        }

        // OIDC server doesn't seem to answer: check roles in token (if present)
        $jws = $this->jwsSerializerManager->unserialize($accessToken);
        $claims = json_decode($jws->getPayload(), true);
        $roles = array_map(static fn (string $role): string => strtolower($role), $claims['realm_access']['roles'] ?? []);

        return \in_array(strtolower(substr($attribute, 5)), $roles, true);
    }
}
