<?php declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\TopBook;

final class TopBookCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private TopBookDataProvider $dataProvider;
    private array $collection;

    public function __construct(TopBookDataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return TopBook::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        try {
            $this->collection = $this->dataProvider->getTopBooks();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to retrieve top books from external source: %s', $e->getMessage()));
        }

        return array_slice($this->collection, $this->getOffset(), $this->getItemsPerPage());
    }

    private function getOffset(array $context = []): int
    {
        $page = (int) ($context['filters']['page'] ?? 1);
        $page = $page < 1 || $page > $this->getLastPage() ? 1 : $page;

        return ($page - 1) * $this->getItemsPerPage();
    }

    public function getLastPage(): float
    {
        return ceil(($this->getTotalItems() / 30));
    }

    /**
     * Gets the number of items in the whole collection.
     */
    public function getTotalItems(): float
    {
        return count($this->collection);
    }

    /**
     * Gets the number of items in the whole collection.
     */
    public function getItemsPerPage(): int
    {
        return 30;
    }
}
