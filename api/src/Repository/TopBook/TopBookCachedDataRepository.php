<?php

declare(strict_types=1);

namespace App\Repository\TopBook;

use App\Entity\TopBook;
use Symfony\Contracts\Cache\CacheInterface;

final class TopBookCachedDataRepository implements TopBookDataInterface
{
    private TopBookDataInterface $repository;
    private CacheInterface $cache;

    public function __construct(TopBookDataInterface $repository, CacheInterface $cache)
    {
        $this->repository = $repository;
        $this->cache = $cache;
    }

    /**
     * Local caching is done so the CSV isn't reloaded at every call.
     *
     * @throws \InvalidArgumentException
     *
     * @return array<int, TopBook>
     */
    public function getTopBooks(): array
    {
        return $this->cache->get('books.sci-fi.top.fr', function () {
            return $this->repository->getTopBooks();
        });
    }
}
