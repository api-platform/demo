<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Filter\ArchivedFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see https://schema.org/Book Documentation on Schema.org
 */
#[ORM\Entity]
#[ApiResource(
    types: ['https://schema.org/Book'],
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Patch(),
        new Delete(security: 'is_granted("ROLE_ADMIN")'),
        new Put(
            uriTemplate: '/books/{id}/generate-cover.{_format}',
            normalizationContext: ['groups' => ['book:read', 'book:cover']],
            output: false,
        ),
    ],
    normalizationContext: ['groups' => ['book:read']],
    mercure: true,
    paginationClientItemsPerPage: true,
)]
#[ApiFilter(ArchivedFilter::class)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'title', 'author', 'isbn', 'publicationDate'])]
#[ApiFilter(PropertyFilter::class)]
class Book implements ArchivableInterface
{
    use ArchivableTrait;

    #[ORM\Id, ORM\GeneratedValue(strategy: 'CUSTOM'), ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(groups: ['book:read'])]
    private ?UuidInterface $id = null;

    /**
     * The ISBN of the book.
     */
    #[ORM\Column(nullable: true)]
    #[ApiProperty(types: ['https://schema.org/isbn'])]
    #[Assert\Isbn]
    #[Groups(groups: ['book:read'])]
    public ?string $isbn = null;

    /**
     * The title of the book.
     */
    #[ORM\Column]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    #[ApiProperty(types: ['https://schema.org/name'])]
    #[Assert\NotBlank]
    #[Groups(groups: ['book:read', 'review:read'])]
    public ?string $title = null;

    /**
     * A description of the item.
     */
    #[ORM\Column(type: 'text')]
    #[ApiProperty(types: ['https://schema.org/description'])]
    #[Assert\NotBlank]
    #[Groups(groups: ['book:read'])]
    public ?string $description = null;

    /**
     * The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably.
     */
    #[ORM\Column]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    #[ApiProperty(types: ['https://schema.org/author'])]
    #[Assert\NotBlank]
    #[Groups(groups: ['book:read'])]
    public ?string $author = null;

    /**
     * The date on which the CreativeWork was created or the item was added to a DataFeed.
     */
    #[ORM\Column(type: 'date')]
    #[ApiProperty(types: ['https://schema.org/dateCreated'])]
    #[Assert\NotNull]
    #[Assert\Type(DateTimeInterface::class)]
    #[Groups(groups: ['book:read'])]
    public ?DateTimeInterface $publicationDate = null;

    /**
     * The book's reviews.
     */
    #[ORM\OneToMany(mappedBy: 'book', targetEntity: Review::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ApiProperty(types: ['https://schema.org/reviews'])]
    #[Groups(groups: ['book:read'])]
    private Collection $reviews;

    /**
     * The book's cover base64 encoded.
     */
    #[Groups(groups: ['book:cover'])]
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
