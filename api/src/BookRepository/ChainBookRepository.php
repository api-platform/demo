<?php

declare(strict_types=1);

namespace App\BookRepository;

use App\BookRepository\Exception\UnsupportedBookException;
use App\Entity\Book;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsAlias]
final readonly class ChainBookRepository implements BookRepositoryInterface
{
    /** @param iterable<RestrictedBookRepositoryInterface> $repositories */
    public function __construct(
        #[AutowireIterator(tag: RestrictedBookRepositoryInterface::TAG)]
        private iterable $repositories,
    ) {
    }

    public function find(string $url): ?Book
    {
        foreach ($this->repositories as $repository) {
            if ($repository->supports($url)) {
                return $repository->find($url);
            }
        }

        throw new UnsupportedBookException();
    }
}
