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
        #[Autowire('%env(OIDC_API_CLIENT_UUID)%')]
        private string $oidcClientUuid,
        #[Autowire('%env(OIDC_SERVER_URL_INTERNAL)%')]
        private string $oidcServerUrl,
    ) {
    }

    public function create(object $resource, UserInterface $owner, array $context = []): void
    {
        $operation = $this->resourceMetadataCollectionFactory->create(resourceClass: $resource::class)->getOperation(
            operationName: $context['operation_name'] ?? null,
            httpOperation: true,
        );
        $shortName = strtolower((string) preg_replace('~(?<=\w)([A-Z])~', '-$1', (string) $operation->getShortName()));
        $resourceIri = $this->iriConverter->getIriFromResource(
            resource: $resource,
            referenceType: UrlGeneratorInterface::ABS_PATH,
            operation: $operation,
        );

        // create resource on OIDC server
        $this->securityAuthorizationClient->request('POST', $this->getResourceEndpoint(), [
            'auth_bearer' => $this->getAccessToken(),
            'json' => [
                'name' => \sprintf('%s_%s', $shortName, $resource->getId()->__toString()),
                'displayName' => \sprintf('%s #%s', $operation->getShortName(), $resource->getId()->__toString()),
                'uris' => [$resourceIri],
                'type' => \sprintf('urn:%s:resources:%s', $this->oidcClientId, $shortName),
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
        $shortName = strtolower((string) preg_replace('~(?<=\w)([A-Z])~', '-$1', (string) $operation->getShortName()));
        $resourceIri = $this->iriConverter->getIriFromResource(
            resource: $resource,
            referenceType: UrlGeneratorInterface::ABS_PATH,
            operation: $operation,
        );

        // retrieve corresponding resource from OIDC server
        $response = $this->securityAuthorizationClient->request(
            'GET',
            $this->getResourceEndpoint(),
            [
                'auth_bearer' => $this->getAccessToken(),
                'query' => [
                    'deep' => 'true',
                    'first' => 0,
                    'max' => 1,
                    'uri' => $resourceIri,
                    'owner' => $owner->getUserIdentifier(),
                    'type' => \sprintf('urn:%s:resources:%s', $this->oidcClientId, $shortName),
                ],
            ]
        );
        $content = $response->toArray();
        $resourceSet = $content[0];

        // delete corresponding resource on OIDC server
        $this->securityAuthorizationClient->request(
            'DELETE',
            \sprintf('%s/%s', $this->getResourceEndpoint(), $resourceSet['_id']),
            [
                'auth_bearer' => $this->getAccessToken(),
            ]
        );
    }

    /**
     * Since Keycloak 26.6.2 the Protection API forces the owner of a resource to the resource
     * server the PAT was issued for, so user-owned resources can only be created through the
     * Admin API.
     *
     * @see https://github.com/keycloak/keycloak/issues/49910
     */
    private function getResourceEndpoint(): string
    {
        return \sprintf(
            '%s/clients/%s/authz/resource-server/resource',
            str_replace('/realms/', '/admin/realms/', $this->oidcServerUrl),
            $this->oidcClientUuid,
        );
    }

    private function getAccessToken(): string
    {
        $response = $this->securityAuthorizationClient->request('POST', 'protocol/openid-connect/token', [
            'body' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->oidcClientId,
                'client_secret' => $this->oidcClientSecret,
            ],
        ]);
        $content = $response->toArray();

        return $content['access_token'];
    }
}
