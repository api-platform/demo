<?php

declare(strict_types=1);

namespace App\DataProvider\Extension;

use ApiPlatform\Core\DataProvider\ArrayPaginator;
use ApiPlatform\Core\DataProvider\Pagination;
use App\Entity\TopBook;

final class TopBookPaginationExtension implements TopBookCollectionExtensionInterface
{
    private array $collection;
    private array $context;
    private Pagination $pagination;

    public function __construct(Pagination $pagination)
    {
        $this->pagination = $pagination;
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

    /**
     * Takes the value set for the "pagination_items_per_page" TopBook annotation
     * parameter or take the default parameter otherwise.
     */
    public function getItemsPerPage(): int
    {
        return $this->pagination->getLimit(TopBook::class);
    }

    /**
     * Takes the value set for the "pagination_enabled" TopBook annotation parameter
     * or take the default parameter otherwise.
     */
    public function isEnabled(): bool
    {
        return $this->pagination->isEnabled(TopBook::class);
    }
}
