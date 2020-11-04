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

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return TopBook::class === $resourceClass;
    }

    /**
     * @throws \RuntimeException
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        try {
            $collection = $this->repository->getTopBooks();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to retrieve top books from external source: %s', $e->getMessage()));
        }

        if (!$this->paginationExtension->isEnabled()) {
            return $collection;
        }

        return $this->paginationExtension->getResult($collection, $resourceClass, $operationName, $context) ;
    }
}
