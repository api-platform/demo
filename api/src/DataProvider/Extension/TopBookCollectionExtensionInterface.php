<?php

declare(strict_types=1);

namespace App\DataProvider\Extension;

interface TopBookCollectionExtensionInterface
{
    /**
     * Returns the final result object.
     */
    public function getResult(array $collection, string $resourceClass, string $operationName = null, array $context = []): iterable;

    /**
     * Tells if the extension is enabled or not.
     */
    public function isEnabled(): bool;
}
