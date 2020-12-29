<?php

declare(strict_types=1);

namespace App\Filter;

interface GenericFilterInterface
{
    /**
     * Tells if the filter can support the resource.
     *
     * @param class-string         $resourceClass
     * @param array<string, mixed> $context
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool;
}
