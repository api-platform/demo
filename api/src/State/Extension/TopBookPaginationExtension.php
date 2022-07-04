<?php

declare(strict_types=1);

namespace App\State\Extension;

use ApiPlatform\State\Pagination\ArrayPaginator;
use ApiPlatform\State\Pagination\Pagination;

final class TopBookPaginationExtension implements TopBookCollectionExtensionInterface
{
    public function __construct(private readonly Pagination $pagination)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getResult(array $collection, string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        [, $offset, $itemPerPage] = $this->pagination->getPagination($resourceClass, $operationName, $context);

        return new ArrayPaginator($collection, $offset, $itemPerPage);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(string $resourceClass = null, string $operationName = null, array $context = []): bool
    {
        return $this->pagination->isEnabled($resourceClass, $operationName, $context);
    }
}
