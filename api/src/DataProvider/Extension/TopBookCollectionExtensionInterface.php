<?php

declare(strict_types=1);

namespace App\DataProvider\Extension;

use App\Entity\TopBook;

interface TopBookCollectionExtensionInterface
{
    /**
     * @param array<int,TopBook> $collection
     *
     * Apply given modification on an array collection.
     */
    public function applyToCollection(array $collection, string $resourceClass, string $operationName = null, array $context = []): void;

    /**
     * Returns the final paginator object.
     */
    public function getResult(array $collection, string $resourceClass, string $operationName = null, array $context = []): iterable;

    /**
     * Tells if pagination is enbaled for the TopBook resource.
     */
    public function isEnabled(): bool;
}
