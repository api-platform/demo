<?php

declare(strict_types=1);

namespace App\DataProvider\Extension;

use ApiPlatform\Core\DataProvider\ArrayPaginator;

final class TopBookPaginationExtension implements TopBookCollectionExtensionInterface
{
    private array $collection;
    private array $context;

    /**
     * This extension only paginates.
     */
    public function applyToCollection(array $collection, string $resourceClass, string $operationName = null, array $context = []): void
    {
    }

    public function getResult(array $collection, string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $this->collection = $collection;
        $this->context = $context;

        return new ArrayPaginator($this->collection, $this->getOffset(), $this->getItemsPerPage());
    }

    public function getLastPage(): int
    {
        return (int) ceil(($this->getTotalItems() / $this->getItemsPerPage()));
    }

    public function getTotalItems(): int
    {
        return count($this->collection);
    }

    private function getOffset(): int
    {
        return ($this->getCurrentPage() - 1) * $this->getItemsPerPage();
    }

    public function getCurrentPage(): int
    {
        $page = (int) ($this->context['filters']['page'] ?? 1);
        $page = $page < 1 || $page > $this->getLastPage() ? 1 : $page;

        return $page;
    }

    public function getItemsPerPage(): int
    {
        return 30;
    }
}
