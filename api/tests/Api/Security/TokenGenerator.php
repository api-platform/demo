<?php

declare(strict_types=1);

namespace App\Tests\Api\Security;

use Jose\Component\Core\JWK;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

final readonly class TokenGenerator
{
    private JWK $jwk;

    public function __construct(
        #[Autowire('@jose.jws_builder.oidc')]
        private JWSBuilder $jwsBuilder,
        #[Autowire('@jose.jws_serializer.oidc')]
        private JWSSerializerManager $jwsSerializerManager,
        #[Autowire('%env(OIDC_JWK)%')]
        string $jwk,
        #[Autowire('%env(OIDC_AUD)%')]
        private string $audience,
        #[Autowire('%env(OIDC_SERVER_URL)%')]
        private string $issuer,
    ) {
        $this->jwk = JWK::createFromJson(json: $jwk);
    }

    public function generateToken(array $claims): string
    {
        // Defaults
        $time = time();
        $sub = Uuid::v7()->__toString();
        $claims += [
            'sub' => $sub,
            'iat' => $time,
            'nbf' => $time,
            'exp' => $time + 3600,
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'given_name' => 'John',
            'family_name' => 'DOE',
        ];
        if (empty($claims['sub'])) {
            $claims['sub'] = $sub;
        }
        if (empty($claims['iat'])) {
            $claims['iat'] = $time;
        }
        if (empty($claims['nbf'])) {
            $claims['nbf'] = $time;
        }
        if (empty($claims['exp'])) {
            $claims['exp'] = $time + 3600;
        }

        return $this->jwsSerializerManager->serialize(
            name: 'jws_compact',
            jws: $this->jwsBuilder
                ->withPayload(json_encode($claims))
                ->addSignature($this->jwk, ['alg' => $this->jwk->get('alg')])
                ->build(),
        );
    }
}
