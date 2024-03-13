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
final class KeycloakProtocolOpenIdConnectTokenIntrospectMock extends MockHttpClient
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
        if (!('POST' === $method && $this->baseUri . 'protocol/openid-connect/token/introspect' === $url)) {
            return $this->decorated->request($method, $url, $options);
        }

        if (!isset($options['body'])) {
            return $this->getInvalidMock();
        }

        // retrieve token from body
        parse_str($options['body'], $body);
        if (!isset($body['token'])) {
            return $this->getInvalidMock();
        }

        $serializerManager = new JWSSerializerManager([new CompactSerializer()]);
        $jws = $serializerManager->unserialize($body['token']);
        $claims = json_decode($jws->getPayload(), true);

        // "authorize" custom claim set in the test
        if (\array_key_exists('authorize', $claims)) {
            return $claims['authorize'] ? $this->getValidMock($claims) : $this->getInvalidMock();
        }

        // no "authorize" custom claim: build roles from user email
        return 'chuck.norris@example.com' === ($claims['email'] ?? null) ? $this->getValidMock($claims) : $this->getInvalidMock();
    }

    private function getValidMock(array $claims): MockResponse
    {
        $roles = ['offline_access', 'uma_authorization', 'user'];
        if ('chuck.norris@example.com' === ($claims['email'] ?? null)) {
            $roles[] = 'admin';
        }

        return new MockResponse(json_encode($claims + [
            'realm_access' => [
                'roles' => $roles,
            ],
        ]), ['http_code' => Response::HTTP_OK]);
    }

    private function getInvalidMock(): MockResponse
    {
        return new MockResponse('', ['http_code' => Response::HTTP_UNAUTHORIZED]);
    }
}
