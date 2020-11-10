<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ApiResource(deprecationReason="Create a Book instead")
 */
class Parchment
{
    /**
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private ?UuidInterface $id = null;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    /**
     * @var string|null The title of the book
     *
     * @Assert\NotBlank
     * @ORM\Column
     */
    public ?string $title = null;

    /**
     * @var string|null A description of the item
     *
     * @Assert\NotBlank
     * @ORM\Column(type="text")
     */
    public ?string $description = null;
}
