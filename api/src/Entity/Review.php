<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\Link;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A review of an item - for example, of a restaurant, movie, or store.
 *
 * @see https://schema.org/Review Documentation on Schema.org
 */
#[ORM\Entity]
#[ApiResource(
    types: ['https://schema.org/Review'],
    normalizationContext: ['groups' => ['review:read']],
    denormalizationContext: ['groups' => ['review:write']],
    mercure: true,
    paginationClientItemsPerPage: true,
)]
#[ApiResource(
    uriTemplate: '/books/{bookId}/reviews.{_format}',
    types: ['https://schema.org/Review'],
    uriVariables: [
        'bookId' => new Link(fromClass: Book::class, identifiers: ['id']),
    ],
    normalizationContext: ['groups' => ['review:read']],
    denormalizationContext: ['groups' => ['review:write']]
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'publicationDate'])]
class Review
{
    #[ORM\Id, ORM\GeneratedValue(strategy: 'CUSTOM'), ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(groups: ['book:read', 'review:read'])]
    private ?UuidInterface $id = null;

    /**
     * The actual body of the review.
     */
    #[ORM\Column(type: 'text')]
    #[ApiProperty(types: ['https://schema.org/reviewBody'])]
    #[Assert\NotBlank]
    #[Groups(groups: ['book:read', 'review:read', 'review:write'])]
    public ?string $body = null;

    /**
     * A rating.
     */
    #[ORM\Column(type: 'smallint')]
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 5)]
    #[Groups(groups: ['review:read', 'review:write'])]
    public ?int $rating = null;

    /**
     * DEPRECATED (use rating now): A letter to rate the book.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(['a', 'b', 'c', 'd'])]
    #[ApiProperty(deprecationReason: 'Use the rating property instead')]
    #[Groups(groups: ['review:read', 'review:write'])]
    public ?string $letter = null;

    /**
     * The item that is being reviewed/rated.
     */
    #[ORM\ManyToOne(targetEntity: Book::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiFilter(SearchFilter::class)]
    #[ApiProperty(types: ['https://schema.org/itemReviewed'])]
    #[Assert\NotNull]
    #[Groups(groups: ['review:read', 'review:write'])]
    private ?Book $book = null;

    /**
     * The author of the review.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[ApiProperty(types: ['https://schema.org/author'])]
    #[Groups(groups: ['review:read', 'review:write'])]
    public ?string $author = null;

    /**
     * Publication date of the review.
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(groups: ['review:read', 'review:write'])]
    public ?DateTimeInterface $publicationDate = null;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function setBook(?Book $book, bool $updateRelation = true): void
    {
        $this->book = $book;
        if (!$updateRelation) {
            return;
        }

        if (null === $book) {
            return;
        }

        $book->addReview($this, false);
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }
}
