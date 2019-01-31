<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A book.
 *
 * @see http://schema.org/Book Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(
 *     iri="http://schema.org/Book",
 *     normalizationContext={"groups"={"book:read"}},
 *     mercure=true,
 *     itemOperations={
 *         "get",
 *         "put",
 *         "delete"={"access_control"="is_granted('ROLE_ADMIN')"},
 *     },
 * )
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter(OrderFilter::class, properties={"id", "title", "author", "isbn", "publicationDate"})
 */
class Book
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

    /**
     * @var string The ISBN of the book
     *
     * @Assert\Isbn
     * @ORM\Column(nullable=true)
     * @Groups("book:read")
     * @ApiProperty(iri="http://schema.org/isbn")
     */
    public $isbn;

    /**
     * @var string The title of the book
     *
     * @ApiFilter(SearchFilter::class, strategy="ipartial")
     * @Assert\NotBlank
     * @ORM\Column
     * @Groups({"book:read", "review:read"})
     * @ApiProperty(iri="http://schema.org/name")
     */
    public $title;

    /**
     * @var string A description of the item
     *
     * @Assert\NotBlank
     * @ORM\Column(type="text")
     * @Groups("book:read")
     * @ApiProperty(iri="http://schema.org/description")
     */
    public $description;

    /**
     * @var string The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably
     *
     * @ApiFilter(SearchFilter::class, strategy="ipartial")
     * @Assert\NotBlank
     * @ORM\Column
     * @Groups("book:read")
     * @ApiProperty(iri="http://schema.org/author")
     */
    public $author;

    /**
     * @var \DateTimeInterface The date on which the CreativeWork was created or the item was added to a DataFeed
     *
     * @Assert\Date
     * @Assert\NotNull
     * @ORM\Column(type="date")
     * @Groups("book:read")
     * @ApiProperty(iri="http://schema.org/dateCreated")
     */
    public $publicationDate;

    /**
     * @var ArrayCollection|Collection|Review[] The book's reviews
     *
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="book", orphanRemoval=true, cascade={"persist", "remove"})
     * @Groups("book:read")
     * @ApiProperty(iri="http://schema.org/reviews")
     */
    private $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function addReview(Review $review, bool $updateRelation = true): void
    {
        if ($this->reviews->contains($review)) {
            return;
        }

        $this->reviews->add($review);
        if ($updateRelation) {
            $review->setBook($this, false);
        }
    }

    public function removeReview(Review $review, bool $updateRelation = true): void
    {
        $this->reviews->removeElement($review);
        if ($updateRelation) {
            $review->setBook(null, false);
        }
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): iterable
    {
        return $this->reviews;
    }
}
