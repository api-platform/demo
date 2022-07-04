<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait ArchivableTrait
{
    #[ORM\Column(type: 'date', nullable: true)]
    public ?DateTimeInterface $archivedAt = null;

    public function archive(): self
    {
        $this->archivedAt = new DateTime();

        return $this;
    }

    public function getArchivedAt(): ?DateTimeInterface
    {
        return $this->archivedAt;
    }
}
