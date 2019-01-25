<?php

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
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    /**
     * @var string The title of the book
     *
     * @Assert\NotBlank
     * @ORM\Column
     */
    public $title;

    /**
     * @var string A description of the item
     *
     * @Assert\NotBlank
     * @ORM\Column(type="text")
     */
    public $description;
}
