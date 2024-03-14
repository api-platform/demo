<?php

declare(strict_types=1);

namespace App\Security\Http\AccessToken\Oidc;

use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\JWSLoader;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\AccessToken\Oidc\Exception\MissingClaimException;
use Symfony\Component\Security\Http\AccessToken\Oidc\OidcTokenHandler;
use Symfony\Component\Security\Http\AccessToken\Oidc\OidcTrait;
use Symfony\Component\Security\Http\Authenticator\FallbackUserLoader;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Completes {@see OidcTokenHandler} with OIDC Discovery and configuration stored in cache.
 */
final class OidcDiscoveryTokenHandler implements AccessTokenHandlerInterface
{
    use OidcTrait;

    public function __construct(
        #[Autowire('@cache.app')]
        private CacheInterface $cache,
        #[Autowire('@jose.jws_loader.oidc')]
        private JWSLoader $jwsLoader,
        private readonly HttpClientInterface $securityAuthorizationClient,
        private string $claim = 'email',
        private int $ttl = 3600,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        try {
            $oidcConfiguration = json_decode($this->cache->get('oidc.configuration', function (ItemInterface $item): string {
                $item->expiresAfter($this->ttl);
                $response = $this->securityAuthorizationClient->request('GET', '.well-known/openid-configuration');

                return $response->getContent();
            }), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            $this->logger?->error('An error occurred while requesting OIDC configuration.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new BadCredentialsException('Invalid credentials.', $e->getCode(), $e);
        }

        try {
            $keyset = JWKSet::createFromJson(
                $this->cache->get('oidc.jwkSet', function (ItemInterface $item) use ($oidcConfiguration): string {
                    $item->expiresAfter($this->ttl);
                    $response = $this->securityAuthorizationClient->request('GET', $oidcConfiguration['jwks_uri']);
                    // we only need signature key
                    $keys = array_filter($response->toArray()['keys'], static fn (array $key) => 'sig' === $key['use']);

                    return json_encode(['keys' => $keys]);
                })
            );
        } catch (\Throwable $e) {
            $this->logger?->error('An error occurred while requesting OIDC certs.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new BadCredentialsException('Invalid credentials.', $e->getCode(), $e);
        }

        try {
            // Decode the token
            $signature = null;
            $jws = $this->jwsLoader->loadAndVerifyWithKeySet(
                token: $accessToken,
                keyset: $keyset,
                signature: $signature,
            );

            $claims = json_decode($jws->getPayload(), true);
            if (empty($claims[$this->claim])) {
                throw new MissingClaimException(sprintf('"%s" claim not found.', $this->claim));
            }

            // UserLoader argument can be overridden by a UserProvider on AccessTokenAuthenticator::authenticate
            return new UserBadge($claims[$this->claim], new FallbackUserLoader(fn () => $this->createUser($claims)), $claims);
        } catch (\Throwable $e) {
            $this->logger?->error('An error occurred while decoding and validating the token.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new BadCredentialsException('Invalid credentials.', $e->getCode(), $e);
        }
    }
}
