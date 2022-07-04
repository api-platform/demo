<?php

declare(strict_types=1);

namespace App\State\Extension;

use App\Entity\TopBook;

interface TopBookCollectionExtensionInterface
{
    /**
     * Returns the final result object.
     *
     * @param array<int, TopBook>  $collection
     * @param array<string, mixed> $context
     *
     * @return iterable<TopBook>
     */
    public function getResult(array $collection, string $resourceClass, string $operationName = null, array $context = []): iterable;

    /**
     * Tells if the extension is enabled or not.
     *
     * @param array<string, mixed> $context
     */
    public function isEnabled(string $resourceClass = null, string $operationName = null, array $context = []): bool;
}
