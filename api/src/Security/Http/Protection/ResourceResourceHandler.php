<?php

declare(strict_types=1);

namespace App\Security\Http\Protection;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ResourceResourceHandler implements ResourceHandlerInterface
{
    public function __construct(
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private IriConverterInterface $iriConverter,
        private HttpClientInterface $securityAuthorizationClient,
        #[Autowire('%env(OIDC_API_CLIENT_ID)%')]
        private string $oidcClientId,
        #[Autowire('%env(OIDC_API_CLIENT_SECRET)%')]
        private string $oidcClientSecret,
    ) {
    }

    public function create(object $resource, UserInterface $owner, array $context = []): void
    {
        $operation = $this->resourceMetadataCollectionFactory->create(resourceClass: $resource::class)->getOperation(
            operationName: $context['operation_name'] ?? null,
            httpOperation: true,
        );
        $shortName = strtolower(preg_replace('~(?<=\w)([A-Z])~', '-$1', $operation->getShortName()));
        $resourceIri = $this->iriConverter->getIriFromResource(
            resource: $resource,
            referenceType: UrlGeneratorInterface::ABS_PATH,
            operation: $operation,
        );

        // create resource_set on OIDC server
        $this->securityAuthorizationClient->request('POST', $this->getResourceRegistrationEndpoint(), [
            'auth_bearer' => $this->getPAT(),
            'json' => [
                'name' => sprintf('%s_%s', $shortName, $resource->getId()->__toString()),
                'displayName' => sprintf('%s #%s', $operation->getShortName(), $resource->getId()->__toString()),
                'uris' => [$resourceIri],
                'type' => sprintf('urn:%s:resources:%s', $this->oidcClientId, $shortName),
                'owner' => $owner->getUserIdentifier(),
            ],
        ]);
    }

    public function delete(object $resource, UserInterface $owner, array $context = []): void
    {
        $operation = $this->resourceMetadataCollectionFactory->create(resourceClass: $resource::class)->getOperation(
            operationName: $context['operation_name'] ?? null,
            httpOperation: true,
        );
        $shortName = strtolower(preg_replace('~(?<=\w)([A-Z])~', '-$1', $operation->getShortName()));
        $resourceIri = $this->iriConverter->getIriFromResource(
            resource: $resource,
            referenceType: UrlGeneratorInterface::ABS_PATH,
            operation: $operation,
        );

        // retrieve corresponding resource_set from OIDC server
        $response = $this->securityAuthorizationClient->request(
            'GET',
            $this->getResourceRegistrationEndpoint(),
            [
                'auth_bearer' => $this->getPAT(),
                'query' => [
                    'deep' => 'true',
                    'first' => 0,
                    'max' => 1,
                    'uri' => $resourceIri,
                    'owner' => $owner->getUserIdentifier(),
                    'type' => sprintf('urn:%s:resources:%s', $this->oidcClientId, $shortName),
                ],
            ]
        );
        $content = $response->toArray();
        $resourceSet = $content[0];

        // delete corresponding resource_set on OIDC server
        $this->securityAuthorizationClient->request(
            'DELETE',
            sprintf('%s/%s', $this->getResourceRegistrationEndpoint(), $resourceSet['_id']),
            [
                'auth_bearer' => $this->getPAT(),
            ]
        );
    }

    /**
     * @see https://www.keycloak.org/docs/latest/authorization_services/index.html#_service_protection_whatis_obtain_pat
     */
    private function getPAT(): string
    {
        $response = $this->securityAuthorizationClient->request('POST', $this->getTokenEndpoint(), [
            'body' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->oidcClientId,
                'client_secret' => $this->oidcClientSecret,
            ],
        ]);
        $content = $response->toArray();

        return $content['access_token'];
    }

    private function getTokenEndpoint(): string
    {
        $response = $this->securityAuthorizationClient->request('GET', '.well-known/openid-configuration');
        $content = $response->toArray();

        // horrible fix for local development, can't find another way to fix it
        // since bitnami/keycloak:^25 returns the configured hostname instead of the requested one
        return \preg_replace('#^https?://localhost/#', 'http://keycloak:8080/', $content['token_endpoint']);
    }

    private function getResourceRegistrationEndpoint(): string
    {
        $response = $this->securityAuthorizationClient->request('GET', '.well-known/uma2-configuration');
        $content = $response->toArray();

        // horrible fix for local development, can't find another way to fix it
        // since bitnami/keycloak:^25 returns the configured hostname instead of the requested one
        return \preg_replace('#^https?://localhost/#', 'http://keycloak:8080/', $content['resource_registration_endpoint']);
    }
}
