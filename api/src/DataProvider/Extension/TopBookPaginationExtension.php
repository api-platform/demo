<?php

declare(strict_types=1);

namespace App\DataProvider\Extension;

use ApiPlatform\Core\DataProvider\ArrayPaginator;
use ApiPlatform\Core\DataProvider\Pagination;

final class TopBookPaginationExtension implements TopBookCollectionExtensionInterface
{
    private Pagination $pagination;

    public function __construct(Pagination $pagination)
    {
        $this->pagination = $pagination;
    }

    /**
     * Returns the paginator object.
     */
    public function getResult(array $collection, string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        [, $offset, $itemPerPage] = $this->pagination->getPagination($resourceClass, $operationName, $context);

        return new ArrayPaginator($collection, $offset, $itemPerPage);
    }

    /**
     * Takes the value set for the "pagination_enabled" TopBook annotation parameter
     * or take the default parameter otherwise.
     */
    public function isEnabled(string $resourceClass = null, string $operationName = null, array $context = []): bool
    {
        return $this->pagination->isEnabled($resourceClass, $operationName, $context);
    }
}
