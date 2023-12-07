<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @deprecated create a Book instead
 */
#[ApiResource(deprecationReason: 'Create a Book instead')]
#[ORM\Entity]
class Parchment
{
    /**
     * @see https://schema.org/identifier
     */
    #[ApiProperty(types: ['https://schema.org/identifier'])]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    private ?Uuid $id = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    /**
     * The title of the book.
     */
    #[Assert\NotBlank(allowNull: false)]
    #[ORM\Column]
    public ?string $title = null;

    /**
     * A description of the item.
     */
    #[Assert\NotBlank(allowNull: false)]
    #[ORM\Column]
    public ?string $description = null;
}
