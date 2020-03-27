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
class Customer
{
    /**
     * @var int The customer Id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string First name
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    public $first_name = '';

    /**
     * @var string Last name
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    public $last_name = '';

    /**
     * @var string Email address
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    public $email = '';

    /**
     * @var string Physical address
     *
     * @ORM\Column
     */
    public $address = '';

    /**
     * @var string Zip Code
     *
     * @ORM\Column
     */
    public $zipcode = '';

    /**
     * @var string City
     *
     * @ORM\Column
     */
    public $city = '';

    /**
     * @var string Avatar
     *
     * @ORM\Column
     */
    public $avatar = '';

    /**
     * @var \DateTimeInterface Birthday
     *
     * @ORM\Column(type="datetime")
     */
    public $birthday = '';

    /**
     * @var \DateTimeInterface First seen time
     *
     * @ORM\Column(type="datetime")
     */
    public $first_seen = '';

    /**
     * @var \DateTimeInterface Last seen time
     *
     * @ORM\Column(type="datetime")
     */
    public $last_seen = '';

    /**
     * @var date Has the customer ordered once
     *
     * @ORM\Column
     */
    public $has_ordered = '';

    /**
     * @var boolean Is the customer subscribed to newsletter
     *
     * @ORM\Column(type="boolean")
     */
    public $has_newsletter = '';

    /**
     * @var int Number of orders the customer has passed
     *
     * @ORM\Column(type="integer")
     */
    public $nb_commands = 0;

    /**
     * @var float Total amount the customer has spent
     *
     * @ORM\Column(type="float")
     */
    public $total_spent = '';

    public function getId(): int
    {
        return $this->id;
    }
}
