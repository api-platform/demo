<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Exception\RuntimeException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\TopBook;
use App\Repository\TopBook\TopBookDataInterface;
use App\State\Extension\TopBookCollectionExtensionInterface;
use Exception;

final class TopBookCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly TopBookDataInterface $repository,
        private readonly TopBookCollectionExtensionInterface $paginationExtension
    ) {
    }

    /**
     * @return iterable<TopBook>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $resourceClass = $operation->getClass();
        $operationName = $operation->getName();

        try {
            $collection = $this->repository->getTopBooks();
        } catch (Exception $exception) {
            throw new RuntimeException(sprintf('Unable to retrieve top books from external source: %s', $exception->getMessage()));
        }

        if (!$this->paginationExtension->isEnabled($resourceClass, $operationName, $context)) {
            return $collection;
        }

        return $this->paginationExtension->getResult($collection, $resourceClass, $operationName, $context);
    }
}
