<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Exception\InvalidIdentifierException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\TopBook;
use App\Repository\TopBook\TopBookDataInterface;
use Exception;
use RuntimeException;

final class TopBookItemProvider implements ProviderInterface
{
    public function __construct(private readonly TopBookDataInterface $repository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?TopBook
    {
        $id = $uriVariables['id'] ?? null;

        if (!is_int($id)) {
            throw new InvalidIdentifierException('Invalid id key type.');
        }

        try {
            $topBooks = $this->repository->getTopBooks();
        } catch (Exception $exception) {
            throw new RuntimeException(sprintf('Unable to retrieve top books from external source: %s', $exception->getMessage()));
        }

        return $topBooks[$id] ?? null;
    }
}
