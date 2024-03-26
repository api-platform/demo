<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;

abstract class OidcVoter extends Voter
{
    public function __construct(
        private readonly RequestStack $requestStack,
        #[Autowire('@security.access_token_extractor.header')]
        private readonly AccessTokenExtractorInterface $accessTokenExtractor,
    ) {
    }

    /**
     * @throws TokenNotFoundException
     */
    protected function getToken(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        // user is authenticated, its token should be valid (validated through AccessTokenAuthenticator)
        $accessToken = $this->accessTokenExtractor->extractAccessToken($request);
        if (!$accessToken) {
            throw new TokenNotFoundException();
        }

        return $accessToken;
    }
}
