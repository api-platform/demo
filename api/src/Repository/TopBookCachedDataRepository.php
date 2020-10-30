<?php

declare(strict_types=1);

namespace App\Repository;

use Symfony\Contracts\Cache\CacheInterface;

final class TopBookCachedDataRepository extends TopBookDataDecorator
{
    private CacheInterface $cache;

    public function __construct(TopBookDataRepository $repository, CacheInterface $cache)
    {
        parent::__construct($repository);
        $this->cache = $cache;
    }

    /**
     * Local caching is done so the CSV isn't reloaded at every call.
     */
    public function getTopBooks(): array
    {
        return $this->cache->get('books.sci-fi.top.fr', function () {
            return $this->repository->getTopBooks();
        });
    }
}
