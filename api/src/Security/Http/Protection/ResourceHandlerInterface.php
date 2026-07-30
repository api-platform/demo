<?php

declare(strict_types=1);

namespace App\Security\Http\Protection;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @see https://www.keycloak.org/docs/latest/authorization_services/index.html#_resource_management
 */
interface ResourceHandlerInterface
{
    /**
     * Creates a user-owned Resource on the OIDC server.
     *
     * @param object $resource the related resource object
     */
    public function create(object $resource, UserInterface $owner, array $context = []): void;

    /**
     * Removes a user-owned Resource from the OIDC server.
     *
     * @param object $resource the related resource object
     */
    public function delete(object $resource, UserInterface $owner, array $context = []): void;
}
