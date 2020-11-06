<?php

declare(strict_types=1);

namespace App\Repository\TopBook;

use App\Entity\TopBook;

interface TopBookDataInterface
{
    /**
     * @return array<int, TopBook>
     */
    public function getTopBooks(): array;
}
