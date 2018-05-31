<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A book.
 *
 * @see http://schema.org/Book Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Book")
 * @ApiFilter(PropertyFilter::class)
 */
class Book
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
     * @var string The ISBN of the book
     *
     * @Assert\Isbn
     * @ORM\Column(nullable=true)
     * @ApiProperty(iri="http://schema.org/isbn")
     */
    public $isbn;

    /**
     * @var string The title of the book
     *
     * @Assert\NotBlank
     * @ORM\Column
     * @ApiProperty(iri="http://schema.org/name")
     */
    public $title;

    /**
     * @var string A description of the item
     *
     * @Assert\NotBlank
     * @ORM\Column(type="text")
     * @ApiProperty(iri="http://schema.org/description")
     */
    public $description;

    /**
     * @var string The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably
     *
     * @Assert\NotBlank
     * @ORM\Column
     * @ApiProperty(iri="http://schema.org/author")
     */
    public $author;

    /**
     * @var \DateTimeInterface The date on which the CreativeWork was created or the item was added to a DataFeed
     *
     * @Assert\Date
     * @Assert\NotNull
     * @ORM\Column(type="date")
     * @ApiProperty(iri="http://schema.org/dateCreated")
     */
    public $publicationDate;

    /**
     * @var Review[] The book's reviews
     *
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="book", orphanRemoval=true, cascade={"all"})
     * @ApiProperty(iri="http://schema.org/reviews")
     */
    private $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): iterable
    {
        return $this->reviews;
    }

    public function addReview(Review $review): void
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->book = $this;
        }
    }

    public function removeReview(Review $review): void
    {
        if ($this->reviews->contains($review)) {
            $review->book = $this;
            $this->reviews->removeElement($review);
        }
    }
}
