<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait ArchivableTrait
{
    /**
     * @var \DateTimeInterface|null The date on which the entity has been archived
     *
     * @Assert\Type(\DateTimeInterface::class)
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
