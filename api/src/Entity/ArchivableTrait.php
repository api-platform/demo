<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ArchivableTrait
{
    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTimeInterface $archivedAt = null;

    public function archive(): self
    {
        $this->archivedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getArchivedAt(): ?\DateTimeInterface
    {
        return $this->archivedAt;
    }
}
