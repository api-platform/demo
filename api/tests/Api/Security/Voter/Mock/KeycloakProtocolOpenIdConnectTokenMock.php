<?php

declare(strict_types=1);

namespace App\Tests\Api\Security\Voter\Mock;

use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[AsDecorator(decorates: 'security.authorization.client', priority: -1)]
final class KeycloakProtocolOpenIdConnectTokenMock extends MockHttpClient
{
    public function __construct(
        #[Autowire('%env(OIDC_SERVER_URL_INTERNAL)%/')]
        private readonly string $baseUri,
        private readonly HttpClientInterface $decorated,
    ) {
        parent::__construct($this->handleRequest(...), $this->baseUri);
    }

    private function handleRequest(string $method, string $url, array $options): ResponseInterface
    {
        if (!('POST' === $method && $this->baseUri . 'protocol/openid-connect/token' === $url)) {
            return $this->decorated->request($method, $url, $options);
        }

        if (!isset($options['normalized_headers']['authorization'][0])) {
            return $this->getInvalidMock();
        }

        $accessToken = preg_replace('/^Authorization: Bearer (.*)$/', '$1', $options['normalized_headers']['authorization'][0]);
        $serializerManager = new JWSSerializerManager([new CompactSerializer()]);
        $jws = $serializerManager->unserialize($accessToken);
        $claims = json_decode($jws->getPayload(), true);

        // "authorize" custom claim set in the test
        if (\array_key_exists('authorize', $claims)) {
            return $claims['authorize'] ? $this->getValidMock() : $this->getInvalidMock();
        }

        // no "authorize" custom claim set, try to detect permission from body
        parse_str($options['body'], $body);
        if (!isset($body['permission'])) {
            return $this->getInvalidMock();
        }

        // if permission starts with "/admin", check for user email in token
        if (preg_match('/^\/admin\//', $body['permission'])) {
            return 'chuck.norris@example.com' === ($claims['email'] ?? null) ? $this->getValidMock() : $this->getInvalidMock();
        }

        // no "authorize" custom claim, permission is not "/admin": consider permission valid
        return $this->getValidMock();
    }

    private function getValidMock(): MockResponse
    {
        return new MockResponse(json_encode(['result' => true]), ['http_code' => Response::HTTP_OK]);
    }

    private function getInvalidMock(): MockResponse
    {
        return new MockResponse(json_encode(['result' => false]), ['http_code' => Response::HTTP_UNAUTHORIZED]);
    }
}
