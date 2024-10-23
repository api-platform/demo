<?php

declare(strict_types=1);

namespace App\BookRepository;

use App\Entity\Book;

interface BookRepositoryInterface
{
    public function find(string $url): ?Book;
}
