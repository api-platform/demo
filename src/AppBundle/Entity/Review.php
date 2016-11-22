<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A review of an item - for example, of a restaurant, movie, or store.
 *
 * @see http://schema.org/Review Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Review")
 */
class Review
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string The actual body of the review
     *
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true)
     * @ApiProperty(iri="http://schema.org/reviewBody")
     */
    private $reviewBody;

    /**
     * @var int
     *
     * @Assert\Type(type="integer")
     * @Assert\Range(min=0, max=5)
     * @ORM\Column(type="smallint")
     */
    private $rating;

    /**
     * @var Book The item that is being reviewed/rated
     *
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Book")
     * @ORM\JoinColumn(nullable=false)
     * @ApiProperty(iri="http://schema.org/itemReviewed")
     */
    private $itemReviewed;

    /**
     * Sets id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets reviewBody.
     *
     * @param string $reviewBody
     *
     * @return $this
     */
    public function setReviewBody($reviewBody)
    {
        $this->reviewBody = $reviewBody;

        return $this;
    }

    /**
     * Gets reviewBody.
     *
     * @return string
     */
    public function getReviewBody()
    {
        return $this->reviewBody;
    }

    /**
     * Sets rating.
     *
     * @param int $rating
     *
     * @return $this
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Gets rating.
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Sets itemReviewed.
     *
     * @param Book $itemReviewed
     *
     * @return $this
     */
    public function setItemReviewed(Book $itemReviewed)
    {
        $this->itemReviewed = $itemReviewed;

        return $this;
    }

    /**
     * Gets itemReviewed.
     *
     * @return Book
     */
    public function getItemReviewed()
    {
        return $this->itemReviewed;
    }
}
