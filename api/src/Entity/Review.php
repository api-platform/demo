<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A review of an item - for example, of a restaurant, movie, or store.
 *
 * @see http://schema.org/Review Documentation on Schema.org
 *
 * @ORM\Entity
 */
#[ApiResource(
    iri: 'http://schema.org/Review',
    mercure: true,
    denormalizationContext: ['groups' => ['review:write']],
    normalizationContext: ['groups' => ['review:read']],
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'publicationDate'])]
class Review
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private ?UuidInterface $id = null;

    /**
     * The actual body of the review.
     *
     * @ORM\Column(type="text")
     */
    #[ApiProperty(iri: 'http://schema.org/reviewBody')]
    #[Assert\NotBlank]
    #[Groups(groups: ['book:read', 'review:read', 'review:write'])]
    public ?string $body = null;

    /**
     * A rating.
     *
     * @ORM\Column(type="smallint")
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 5)]
    #[Groups(groups: ['review:read', 'review:write'])]
    public ?int $rating = null;

    /**
     * DEPRECATED (use rating now): A letter to rate the book.
     *
     * @Assert\Choice({"a", "b", "c", "d"})
     * @ORM\Column(type="string", nullable=true)
     */
    #[ApiProperty(deprecationReason: 'Use the rating property instead')]
    #[Groups(groups: ['review:read', 'review:write'])]
    public ?string $letter = null;

    /**
     * The item that is being reviewed/rated.
     *
     * @ORM\ManyToOne(targetEntity=Book::class, inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     */
    #[ApiFilter(SearchFilter::class)]
    #[ApiProperty(iri: 'http://schema.org/itemReviewed')]
    #[Assert\NotNull]
    #[Groups(groups: ['review:read', 'review:write'])]
    private ?Book $book = null;

    /**
     * The author of the review.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    #[ApiProperty(iri: 'http://schema.org/author')]
    #[Groups(groups: ['review:read', 'review:write'])]
    public ?string $author = null;

    /**
     * Publication date of the review.
     *
     * @ORM\Column(nullable=true, type="datetime")
     */
    #[Groups(groups: ['review:read', 'review:write'])]
    public ?\DateTimeInterface $publicationDate = null;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function setBook(?Book $book, bool $updateRelation = true): void
    {
        $this->book = $book;
        if ($updateRelation && null !== $book) {
            $book->addReview($this, false);
        }
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }
}
