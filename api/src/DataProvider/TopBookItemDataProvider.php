<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\InvalidIdentifierException;
use App\Entity\TopBook;
use App\Repository\TopBook\TopBookDataInterface;

final class TopBookItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private TopBookDataInterface $repository)
    {
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
     * @throws InvalidIdentifierException
     *
     * @phpstan-ignore-next-line
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?TopBook
    {
        if (!is_int($id)) {
            throw new InvalidIdentifierException('Invalid id key type.');
        }

        try {
            $topBooks = $this->repository->getTopBooks();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to retrieve top books from external source: %s', $e->getMessage()));
        }

        return $topBooks[$id] ?? null;
    }
}
