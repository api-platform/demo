<?php

declare(strict_types=1);

namespace App\Security\Http\Protection;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @see https://www.keycloak.org/docs/latest/authorization_services/index.html#_service_protection_api
 */
interface ResourceHandlerInterface
{
    /**
     * Creates a ResourceSet on the OIDC server.
     *
     * @see https://www.keycloak.org/docs/latest/authorization_services/index.html#_service_protection_resources_api
     *
     * @param object $resource the related resource object
     */
    public function create(object $resource, UserInterface $owner, array $context = []): void;

    /**
     * Removes a ResourceSet from the OIDC server.
     *
     * @see https://www.keycloak.org/docs/latest/authorization_services/index.html#_service_protection_resources_api
     *
     * @param object $resource the related resource object
     */
    public function delete(object $resource, UserInterface $owner, array $context = []): void;
}
