<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ApiResource(deprecationReason="Create a Book instead")
 */
class Parchment
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function getId(): ?int
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
