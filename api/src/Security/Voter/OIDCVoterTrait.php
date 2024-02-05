<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

trait OIDCVoterTrait
{
    /**
     * @throws BadCredentialsException
     */
    private function getToken(TokenInterface $token): bool|string
    {
        // ensure user is authenticated
        if (!$token->getUser() instanceof UserInterface) {
            return false;
        }

        $request = $this->requestStack->getCurrentRequest();

        // user is authenticated, its token should be valid (validated through AccessTokenAuthenticator)
        // todo is there a better way to retrieve the access-token?
        $accessToken = $this->accessTokenExtractor->extractAccessToken($request);
        if (!$accessToken) {
            return false;
        }

        return $accessToken;
    }
}
