<?php

declare(strict_types=1);

namespace App\Repository;

/**
 * Interface Component
 */
interface TopBookDataInterface
{
    public function getTopBooks(): array;
}
