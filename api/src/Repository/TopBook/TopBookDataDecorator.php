<?php

declare(strict_types=1);

namespace App\Repository\TopBook;

class TopBookDataDecorator implements TopBookDataInterface
{
    protected TopBookDataInterface $repository;

    public function __construct(TopBookDataInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getTopBooks(): array
    {
        return $this->repository->getTopBooks();
    }
}
