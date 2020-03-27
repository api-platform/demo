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
class Product
{
    /**
     * @var int The product Id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    public $reference;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    public $width = '';

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    public $height = '';

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    public $price = '';

    /**
     * @var string
     *
     * @ORM\Column
     */
    public $thumbnail = '';

    /**
     * @var string
     *
     * @ORM\Column
     */
    public $image = '';

    /**
     * @var string
     *
     * @ORM\Column
     */
    public $description = '';


    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    public $stock = 0;

    public function getId(): int
    {
        return $this->id;
    }
}
