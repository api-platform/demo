<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Serializer\IriTransformerNormalizer;
use App\State\Processor\ReviewPersistProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A review of an item - for example, of a restaurant, movie, or store.
 *
 * @see https://schema.org/Review
 */
#[ORM\Entity]
#[ApiResource(
    types: ['https://schema.org/Review'],
    operations: [
        new GetCollection(
            uriTemplate: '/admin/reviews{._format}',
            itemUriTemplate: '/admin/reviews/{id}{._format}'
        ),
        new Get(
            uriTemplate: '/admin/reviews/{id}{._format}'
        ),
        new Patch(
            uriTemplate: '/admin/reviews/{id}{._format}',
            itemUriTemplate: '/admin/reviews/{id}{._format}'
        ),
        new Delete(
            uriTemplate: '/admin/reviews/{id}{._format}'
        ),
    ],
    normalizationContext: [
        'groups' => ['Review:read', 'Review:read:admin'],
        IriTransformerNormalizer::CONTEXT_KEY => [
            'book' => '/admin/books/{id}{._format}',
            'user' => '/admin/users/{id}{._format}',
        ],
    ],
    denormalizationContext: ['groups' => ['Review:write']],
    mercure: true,
    security: 'is_granted("ROLE_ADMIN")'
)]
#[ApiResource(
    types: ['https://schema.org/Review'],
    uriTemplate: '/books/{bookId}/reviews{._format}',
    uriVariables: [
        'bookId' => new Link(toProperty: 'book', fromClass: Book::class),
    ],
    operations: [
        new GetCollection(
            filters: [], // disable filters
            itemUriTemplate: '/books/{bookId}/reviews/{id}{._format}'
        ),
        new Get(
            uriTemplate: '/books/{bookId}/reviews/{id}{._format}',
            uriVariables: [
                'bookId' => new Link(toProperty: 'book', fromClass: Book::class),
                'id' => new Link(fromClass: Review::class),
            ],
        ),
        new Post(
            security: 'is_granted("ROLE_USER")',
            processor: ReviewPersistProcessor::class,
            itemUriTemplate: '/books/{bookId}/reviews/{id}{._format}'
        ),
        new Patch(
            uriTemplate: '/books/{bookId}/reviews/{id}{._format}',
            uriVariables: [
                'bookId' => new Link(toProperty: 'book', fromClass: Book::class),
                'id' => new Link(fromClass: Review::class),
            ],
            itemUriTemplate: '/books/{bookId}/reviews/{id}{._format}',
            security: 'is_granted("ROLE_USER") and user == object.user'
        ),
        new Delete(
            uriTemplate: '/books/{bookId}/reviews/{id}{._format}',
            uriVariables: [
                'bookId' => new Link(toProperty: 'book', fromClass: Book::class),
                'id' => new Link(fromClass: Review::class),
            ],
            security: 'is_granted("ROLE_USER") and user == object.user'
        ),
    ],
    normalizationContext: [
        IriTransformerNormalizer::CONTEXT_KEY => [
            'book' => '/books/{id}{._format}',
            'user' => '/users/{id}{._format}',
        ],
        'groups' => ['Review:read'],
    ],
    denormalizationContext: ['groups' => ['Review:write']]
)]
class Review
{
    /**
     * @see https://schema.org/identifier
     */
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ApiProperty(types: ['https://schema.org/identifier'])]
    private ?Uuid $id = null;

    /**
     * @see https://schema.org/author
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[ApiProperty(types: ['https://schema.org/author'])]
    #[Groups(groups: ['Review:read'])]
    public ?User $user = null;

    /**
     * @see https://schema.org/itemReviewed
     */
    #[ORM\ManyToOne(targetEntity: Book::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[ApiProperty(types: ['https://schema.org/itemReviewed'])]
    #[Groups(groups: ['Review:read', 'Review:write'])]
    #[Assert\NotNull]
    public ?Book $book = null;

    /**
     * @see https://schema.org/datePublished
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[ApiProperty(types: ['https://schema.org/datePublished'])]
    #[Groups(groups: ['Review:read'])]
    public ?\DateTimeInterface $publishedAt = null;

    /**
     * @see https://schema.org/reviewBody
     */
    #[ORM\Column]
    #[ApiProperty(types: ['https://schema.org/reviewBody'])]
    #[Groups(groups: ['Review:read', 'Review:write'])]
    #[Assert\NotBlank(allowNull: false)]
    public ?string $body = null;

    /**
     * @see https://schema.org/reviewRating
     */
    #[ORM\Column(type: 'smallint')]
    #[ApiFilter(NumericFilter::class)]
    #[ApiProperty(types: ['https://schema.org/reviewRating'])]
    #[Groups(groups: ['Review:read', 'Review:write'])]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 5)]
    public ?int $rating = null;

    /**
     * @deprecated use the rating property instead
     */
    #[ORM\Column(nullable: true)]
    #[ApiProperty(deprecationReason: 'Use the rating property instead.')]
    #[Groups(groups: ['Review:read', 'Review:write'])]
    #[Assert\Choice(['a', 'b', 'c', 'd'])]
    public ?string $letter = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}
