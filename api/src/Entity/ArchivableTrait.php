<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ArchivableTrait
{
    /**
     * @var \DateTimeInterface|null The date when the entity has been archived
     *
     * @ORM\Column(type="date", nullable=true)
     */
    public ?\DateTimeInterface $archivedAt = null;

    public function archive(): self
    {
        $this->archivedAt = new \DateTime();

        return $this;
    }

    public function getArchivedAt(): ?\DateTimeInterface
    {
        return $this->archivedAt;
    }
}
