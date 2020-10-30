<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\InvalidIdentifierException;
use App\Entity\TopBook;
use App\Repository\TopBookDataInterface;

final class TopBookItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private TopBookDataInterface $repository;

    public function __construct(TopBookDataInterface $repository)
    {
        $this->repository = $repository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return TopBook::class === $resourceClass;
    }

    /**
     * @throws InvalidIdentifierException
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?TopBook
    {
        if (!is_int($id)) {
            throw new InvalidIdentifierException('Invalid id key type.');
        }
        $id = $this->checkId($id);
        try {
            $topBooks = $this->repository->getTopBooks();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to retrieve top books from external source: %s', $e->getMessage()));
        }

        if (!array_key_exists($id, $topBooks)) {
            throw new InvalidIdentifierException(sprintf('Top book ranked n°"%d" not found.', $id));
        }

        return $topBooks[$id];
    }

    /**
     * @throws InvalidIdentifierException
     */
    private function checkId(int $id): int
    {
        // Non int identifiers are cast to int(0)
        if (0 === $id) {
            throw new InvalidIdentifierException('Invalid id value.');
        }

        if ($id < 1 || $id > 100) {
            throw new InvalidIdentifierException('Only first 100 top books are available.');
        }

        return $id;
    }
}
