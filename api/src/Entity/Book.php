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
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see https://schema.org/Book Documentation on Schema.org
 */
#[ORM\Entity]
#[ApiResource(
    iri: 'https://schema.org/Book',
    itemOperations: [
        'get',
        'put',
        'patch',
        'delete' => ['security' => 'is_granted("ROLE_ADMIN")'],
        'generate_cover' => [
            'method' => 'PUT',
            'path' => '/books/{id}/generate-cover',
            'output' => false,
            'messenger' => true,
            'normalizationContext' => ['groups' => ['book:read', 'book:cover']],
        ],
    ],
    mercure: true,
    normalizationContext: ['groups' => ['book:read']],
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
    private ?UuidInterface $id = null;

    /**
     * The ISBN of the book.
     */
    #[ORM\Column(nullable: true)]
    #[ApiProperty(iri: 'https://schema.org/isbn')]
    #[Assert\Isbn]
    #[Groups(groups: ['book:read'])]
    public ?string $isbn = null;

    /**
     * The title of the book.
     */
    #[ORM\Column]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    #[ApiProperty(iri: 'https://schema.org/name')]
    #[Assert\NotBlank]
    #[Groups(groups: ['book:read', 'review:read'])]
    public ?string $title = null;

    /**
     * A description of the item.
     */
    #[ORM\Column(type: 'text')]
    #[ApiProperty(iri: 'https://schema.org/description')]
    #[Assert\NotBlank]
    #[Groups(groups: ['book:read'])]
    public ?string $description = null;

    /**
     * The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably.
     */
    #[ORM\Column]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    #[ApiProperty(iri: 'https://schema.org/author')]
    #[Assert\NotBlank]
    #[Groups(groups: ['book:read'])]
    public ?string $author = null;

    /**
     * The date on which the CreativeWork was created or the item was added to a DataFeed.
     */
    #[ORM\Column(type: 'date')]
    #[ApiProperty(iri: 'https://schema.org/dateCreated')]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeInterface::class)]
    #[Groups(groups: ['book:read'])]
    public ?\DateTimeInterface $publicationDate = null;

    /**
     * The book's reviews.
     */
    #[ORM\OneToMany(mappedBy: 'book', targetEntity: Review::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ApiProperty(iri: 'https://schema.org/reviews')]
    #[ApiSubresource]
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
