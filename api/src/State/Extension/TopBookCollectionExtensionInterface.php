<?php

declare(strict_types=1);

namespace App\State\Extension;

use ApiPlatform\Metadata\Operation;
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
    public function getResult(array $collection, string $resourceClass, ?Operation $operation = null, array $context = []): iterable;

    /**
     * Tells if the extension is enabled or not.
     *
     * @param array<string, mixed> $context
     */
    public function isEnabled(string $resourceClass = null, ?Operation $operation = null, array $context = []): bool;
}
