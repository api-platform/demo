<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A customer
 *
 * @ApiResource
 * @ORM\Entity
 */
class Basket
{
    /**
     * @var int The basket Id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

   /**
     * @var Command
     *
     * @ORM\ManyToOne(targetEntity="Command", inversedBy="basket")
     */
    public $command;

   /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Product")
     */
    public $product;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    public $quantity = 1;

    public function getId(): int
    {
        return $this->id;
    }
}
