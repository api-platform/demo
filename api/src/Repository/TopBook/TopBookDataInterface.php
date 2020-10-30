<?php

declare(strict_types=1);

namespace App\Repository\TopBook;

/**
 * Interface Component
 */
interface TopBookDataInterface
{
    public function getTopBooks(): array;
}
