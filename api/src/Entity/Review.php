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
 * @ApiResource(
 *     iri="http://schema.org/Review",
 *     denormalizationContext={"groups": {"review:write"}},
 *     normalizationContext={"groups": {"review:read"}}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "publicationDate"})
 */
class Review
{
    /**
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private ?UuidInterface $id = null;

    /**
     * @var string|null The actual body of the review
     *
     * @Assert\NotBlank
     * @ORM\Column(type="text")
     * @Groups({"book:read", "review:read", "review:write"})
     * @ApiProperty(iri="http://schema.org/reviewBody")
     */
    public ?string $body = null;

    /**
     * @var int|null A rating
     *
     * @Assert\NotBlank
     * @Assert\Range(min=0, max=5)
     * @ORM\Column(type="smallint")
     * @Groups({"review:read", "review:write"})
     */
    public ?int $rating = null;

    /**
     * @var string|null DEPRECATED (use rating now): A letter to rate the book
     *
     * @Assert\Choice({"a", "b", "c", "d"})
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"review:read", "review:write"})
     * @ApiProperty(deprecationReason="Use the rating property instead")
     */
    public ?string $letter = null;

    /**
     * @var Book|null The item that is being reviewed/rated
     *
     * @ApiFilter(SearchFilter::class)
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity=Book::class, inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"review:read", "review:write"})
     * @ApiProperty(iri="http://schema.org/itemReviewed")
     */
    private ?Book $book = null;

    /**
     * @var string|null The author of the review
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"review:read", "review:write"})
     * @ApiProperty(iri="http://schema.org/author")
     */
    public ?string $author = null;

    /**
     * @var \DateTimeInterface|null Publication date of the review
     *
     * @Groups({"review:read", "review:write"})
     * @ORM\Column(nullable=true, type="datetime")
     */
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
