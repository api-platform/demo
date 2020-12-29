<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Filter\ArchivedFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
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
 *         "patch",
 *         "delete"={"security"="is_granted('ROLE_ADMIN')"},
 *         "generate_cover"={
 *             "method"="PUT",
 *             "path"="/books/{id}/generate-cover",
 *             "output"=false,
 *             "messenger"=true,
 *             "normalizationContext"={"groups"={"book:read", "book:cover"}}
 *         },
 *     }
 * )
 * @ApiFilter(PropertyFilter::class)
 * @ApiFilter(OrderFilter::class, properties={"id", "title", "author", "isbn", "publicationDate"})
 * @ApiFilter(ArchivedFilter::class)
 */
class Book implements ArchivableInterface
{
    use ArchivableTrait;

    /**
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private ?UuidInterface $id = null;

    /**
     * @var string|null The ISBN of the book
     *
     * @Assert\Isbn
     * @ORM\Column(nullable=true)
     * @Groups("book:read")
     * @ApiProperty(iri="http://schema.org/isbn")
     */
    public ?string $isbn = null;

    /**
     * @var string|null The title of the book
     *
     * @ApiFilter(SearchFilter::class, strategy="ipartial")
     * @Assert\NotBlank
     * @ORM\Column
     * @Groups({"book:read", "review:read"})
     * @ApiProperty(iri="http://schema.org/name")
     */
    public ?string $title = null;

    /**
     * @var string|null A description of the item
     *
     * @Assert\NotBlank
     * @ORM\Column(type="text")
     * @Groups("book:read")
     * @ApiProperty(iri="http://schema.org/description")
     */
    public ?string $description = null;

    /**
     * @var string|null The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably
     *
     * @ApiFilter(SearchFilter::class, strategy="ipartial")
     * @Assert\NotBlank
     * @ORM\Column
     * @Groups("book:read")
     * @ApiProperty(iri="http://schema.org/author")
     */
    public ?string $author = null;

    /**
     * @var \DateTimeInterface|null The date on which the CreativeWork was created or the item was added to a DataFeed
     *
     * @Assert\Type(\DateTimeInterface::class)
     * @Assert\NotNull
     * @ORM\Column(type="date")
     * @Groups("book:read")
     * @ApiProperty(iri="http://schema.org/dateCreated")
     */
    public ?\DateTimeInterface $publicationDate = null;

    /**
     * @var Collection<int, Review> The book's reviews
     *
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="book", orphanRemoval=true, cascade={"persist", "remove"})
     * @Groups("book:read")
     * @ApiProperty(iri="http://schema.org/reviews")
     * @ApiSubresource
     */
    private Collection $reviews;

    /**
     * @var string|null The book's cover base64 encoded
     *
     * @Groups("book:cover")
     */
    public ?string $cover = null;

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
     * @return Collection<int, Review>
     */
    public function getReviews(): iterable
    {
        return $this->reviews;
    }
}
