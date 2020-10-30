<?php

declare(strict_types=1);

namespace App\Repository\TopBook;

interface TopBookDataInterface
{
    public function getTopBooks(): array;
}
