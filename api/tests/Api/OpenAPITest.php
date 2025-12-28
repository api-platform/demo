<?php

declare(strict_types=1);

namespace Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

final class OpenAPITest extends ApiTestCase
{
    #[Test]
    public function jsonLdSchemaHasHydraItemBaseSchema(): void
    {
        $client = self::createClient();
        $client->request('GET', '/docs', [
            'headers' => [
                'Accept' => 'application/vnd.openapi+json',
            ],
        ]);

        self::assertResponseIsSuccessful();

        $openapi = $r->toArray();

        self::assertArrayHasKey('components', $openapi);
        self::assertArrayHasKey('schemas', $openapi['components']);
        self::assertArrayHasKey('Parchment.jsonld', $openapi['components']['schemas']);

        $parchmentJsonLdSchema = $openapi['components']['schemas']['Parchment.jsonld'];
        self::assertArrayHasKey('allOf', $parchmentJsonLdSchema);
        self::assertContains(
            ['$ref' => '#/components/schemas/HydraItemBaseSchema'],
            $parchmentJsonLdSchema['allOf']
        );
    }
}
