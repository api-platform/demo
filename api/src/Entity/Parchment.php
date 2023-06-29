<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @deprecated create a Book instead
 */
#[ORM\Entity]
#[ApiResource(deprecationReason: 'Create a Book instead')]
class Parchment
{
    /**
     * @see https://schema.org/identifier
     */
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ApiProperty(types: ['https://schema.org/identifier'])]
    private ?Uuid $id = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    /**
     * The title of the book.
     */
    #[ORM\Column]
    #[Assert\NotBlank(allowNull: false)]
    public ?string $title = null;

    /**
     * A description of the item.
     */
    #[ORM\Column]
    #[Assert\NotBlank(allowNull: false)]
    public ?string $description = null;
}
