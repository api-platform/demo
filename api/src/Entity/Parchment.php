<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(deprecationReason: 'Create a Book instead')]
class Parchment
{
    #[ORM\Id, ORM\GeneratedValue(strategy: 'CUSTOM'), ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?UuidInterface $id = null;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    /**
     * The title of the book.
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    public ?string $title = null;

    /**
     * A description of the item.
     */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    public ?string $description = null;
}
