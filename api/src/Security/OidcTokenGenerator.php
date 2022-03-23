<?php

declare(strict_types=1);

namespace App\Security;

use Jose\Component\Core\Algorithm;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * Generates a token for specified claims.
 *
 * @experimental
 */
#[When('test')]
#[Autoconfigure(public: true)]
final class OidcTokenGenerator
{
    public function __construct(
        #[Autowire('@security.access_token_handler.oidc.signature.ES256')]
        private Algorithm $signatureAlgorithm,
        #[Autowire('@app.security.jwk')]
        private JWK $jwk,
        #[Autowire('%app.oidc.aud%')]
        private string $audience,
        #[Autowire('%env(OIDC_SERVER_URL)%')]
        private string $issuer
    ) {
    }

    public function generate(array $claims): string
    {
        // Defaults
        $time = time();
        $claims += [
            'iat' => $time,
            'nbf' => $time,
            'exp' => $time + 3600,
            'iss' => $this->issuer,
            'aud' => $this->audience,
        ];
        if (empty($claims['iat'])) {
            $claims['iat'] = $time;
        }
        if (empty($claims['nbf'])) {
            $claims['nbf'] = $time;
        }
        if (empty($claims['exp'])) {
            $claims['exp'] = $time + 3600;
        }

        return (new CompactSerializer())->serialize((new JWSBuilder(new AlgorithmManager([
            $this->signatureAlgorithm,
        ])))->create()
            ->withPayload(json_encode($claims))
            ->addSignature($this->jwk, ['alg' => $this->signatureAlgorithm->name()])
            ->build()
        );
    }
}
