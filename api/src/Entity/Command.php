<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A customer
 *
 * @ApiResource
 * @ORM\Entity
 */
class Command
{
    /**
     * @var int The command Id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string Reference name
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    public $reference = '';

    /**
     * @var \DateTimeInterface Date
     *
     * @ORM\Column(type="datetime")
     */
    public $date = '';

   /**
     * @var Customer The book this review is about.
     *
     * @ORM\ManyToOne(targetEntity="Customer")
     */
    public $customer;


    /**
     * @var Basket[]
     * @ApiProperty(attributes={"fetchEager": true})
     * @ORM\OneToMany(targetEntity="Basket", mappedBy="command", cascade={"persist", "remove"})
     */
    public $basket;

    /**
     * @var float Total amount the customer has spent
     *
     * @ORM\Column(type="float")
     */
    public $total_ex_taxes;


    /**
     * @var float Total amount the customer has spent
     *
     * @ORM\Column(type="float")
     */
    public $delivery_fees;

    /**
     * @var float Total amount the customer has spent
     *
     * @ORM\Column(type="float")
     */
    public $tax_rate;

    /**
     * @var float Total amount the customer has spent
     *
     * @ORM\Column(type="float")
     */
    public $taxes;

    /**
     * @var float Total amount the customer has spent
     *
     * @ORM\Column(type="float")
     */
    public $total;

    /**
     * @var string Reference name
     *
     * @ORM\Column
     */
    public $status;

    /**
     * @var boolean Is the customer subscribed to newsletter
     *
     * @ORM\Column(type="boolean")
     */
    public $returned;

    public function getId(): int
    {
        return $this->id;
    }
}
