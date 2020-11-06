<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\DataProvider\Extension\TopBookCollectionExtensionInterface;
use App\Entity\TopBook;
use App\Repository\TopBook\TopBookDataInterface;

final class TopBookCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private TopBookDataInterface $repository;
    private TopBookCollectionExtensionInterface $paginationExtension;

    public function __construct(TopBookDataInterface $repository, TopBookCollectionExtensionInterface $topBookPaginationExtension)
    {
        $this->repository = $repository;
        $this->paginationExtension = $topBookPaginationExtension;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return TopBook::class === $resourceClass;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws \RuntimeException
     *
     * @return iterable<TopBook>
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        try {
            $collection = $this->repository->getTopBooks();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to retrieve top books from external source: %s', $e->getMessage()));
        }

        if (!$this->paginationExtension->isEnabled($resourceClass, $operationName, $context)) {
            return $collection;
        }

        return $this->paginationExtension->getResult($collection, $resourceClass, $operationName, $context);
    }
}
