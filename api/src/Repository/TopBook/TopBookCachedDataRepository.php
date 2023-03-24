<?php

declare(strict_types=1);

namespace App\Repository\TopBook;

use App\Entity\TopBook;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Contracts\Cache\CacheInterface;

#[AsDecorator(decorates: TopBookDataRepository::class)]
final class TopBookCachedDataRepository implements TopBookDataInterface
{
    public function __construct(
        private readonly TopBookDataInterface $repository,
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * Local caching is done so the CSV isn't reloaded at every call.
     *
     * @return array<int, TopBook>
     *
     * @throws \InvalidArgumentException
     */
    public function getTopBooks(): array
    {
        return $this->cache->get('books.sci-fi.top.fr', function (): array {
            return $this->repository->getTopBooks();
        });
    }
}
