<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
interface ArchivableInterface
{
    public function getArchivedAt(): ?DateTimeInterface;
}
