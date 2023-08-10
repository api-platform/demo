<?php

declare(strict_types=1);

namespace App\Tests\Api\Trait;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Symfony\Component\Uid\Uuid;

trait SecurityTrait
{
    public function generateToken(array $claims): string
    {
        $container = static::getContainer();
        $signatureAlgorithm = $container->get('security.access_token_handler.oidc.signature.ES256');
        $jwk = $container->get('app.security.jwk');
        $audience = $container->getParameter('app.oidc.aud');
        $issuer = $container->getParameter('app.oidc.issuer');

        // Defaults
        $time = time();
        $sub = Uuid::v7()->__toString();
        $claims += [
            'sub' => $sub,
            'iat' => $time,
            'nbf' => $time,
            'exp' => $time + 3600,
            'iss' => $issuer,
            'aud' => $audience,
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

        return (new CompactSerializer())->serialize((new JWSBuilder(new AlgorithmManager([
            $signatureAlgorithm,
        ])))->create()
            ->withPayload(json_encode($claims))
            ->addSignature($jwk, ['alg' => $signatureAlgorithm->name()])
            ->build()
        );
    }
}
