<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\NotExposed;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\CreateProvider;
use App\Repository\ReviewRepository;
use App\Serializer\IriTransformerNormalizer;
use App\State\Processor\ReviewPersistProcessor;
use App\State\Processor\ReviewRemoveProcessor;
use Doctrine\DBAL\Types\Types;
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
#[ApiResource(
    types: ['https://schema.org/Review'],
    order: ['publishedAt' => 'DESC'],
    operations: [
        new GetCollection(
            uriTemplate: '/admin/reviews{._format}',
            itemUriTemplate: '/admin/reviews/{id}{._format}',
            filters: ['app.filter.review.admin.search', 'app.filter.review.admin.numeric']
        ),
        new Get(
            uriTemplate: '/admin/reviews/{id}{._format}'
        ),
        // https://github.com/api-platform/admin/issues/370
        new Put(
            uriTemplate: '/admin/reviews/{id}{._format}',
            // Mercure publish is done manually in MercureProcessor through ReviewPersistProcessor
            processor: ReviewPersistProcessor::class
        ),
        new Delete(
            uriTemplate: '/admin/reviews/{id}{._format}',
            // Mercure publish is done manually in MercureProcessor through ReviewRemoveProcessor
            processor: ReviewRemoveProcessor::class
        ),
    ],
    normalizationContext: [
        IriTransformerNormalizer::CONTEXT_KEY => [
            'book' => '/admin/books/{id}{._format}',
            'user' => '/admin/users/{id}{._format}',
        ],
        'skip_null_values' => true,
        'groups' => ['Review:read', 'Review:read:admin'],
    ],
    denormalizationContext: ['groups' => ['Review:write']],
    security: 'is_granted("ROLE_ADMIN")'
)]
#[ApiResource(
    types: ['https://schema.org/Review'],
    order: ['publishedAt' => 'DESC'],
    uriTemplate: '/books/{bookId}/reviews{._format}',
    uriVariables: [
        'bookId' => new Link(toProperty: 'book', fromClass: Book::class),
    ],
    operations: [
        new GetCollection(
            itemUriTemplate: '/books/{bookId}/reviews/{id}{._format}',
            paginationClientItemsPerPage: true
        ),
        new NotExposed(
            uriTemplate: '/books/{bookId}/reviews/{id}{._format}',
            uriVariables: [
                'bookId' => new Link(toProperty: 'book', fromClass: Book::class),
                'id' => new Link(fromClass: Review::class),
            ]
        ),
        new Post(
            security: 'is_granted("ROLE_USER")',
            // Mercure publish is done manually in MercureProcessor through ReviewPersistProcessor
            processor: ReviewPersistProcessor::class,
            provider: CreateProvider::class,
            itemUriTemplate: '/books/{bookId}/reviews/{id}{._format}'
        ),
        new Patch(
            uriTemplate: '/books/{bookId}/reviews/{id}{._format}',
            uriVariables: [
                'bookId' => new Link(toProperty: 'book', fromClass: Book::class),
                'id' => new Link(fromClass: Review::class),
            ],
            security: 'is_granted("ROLE_USER") and user == object.user',
            // Mercure publish is done manually in MercureProcessor through ReviewPersistProcessor
            processor: ReviewPersistProcessor::class
        ),
        new Delete(
            uriTemplate: '/books/{bookId}/reviews/{id}{._format}',
            uriVariables: [
                'bookId' => new Link(toProperty: 'book', fromClass: Book::class),
                'id' => new Link(fromClass: Review::class),
            ],
            security: 'is_granted("ROLE_USER") and user == object.user',
            // Mercure publish is done manually in MercureProcessor through ReviewRemoveProcessor
            processor: ReviewRemoveProcessor::class
        ),
    ],
    normalizationContext: [
        IriTransformerNormalizer::CONTEXT_KEY => [
            'book' => '/books/{id}{._format}',
            'user' => '/users/{id}{._format}',
        ],
        'skip_null_values' => true,
        'groups' => ['Review:read'],
    ],
    denormalizationContext: ['groups' => ['Review:write']]
)]
#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    /**
     * @see https://schema.org/identifier
     */
    #[ApiProperty(types: ['https://schema.org/identifier'])]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    private ?Uuid $id = null;

    /**
     * @see https://schema.org/author
     */
    #[ApiProperty(types: ['https://schema.org/author'])]
    #[Groups(groups: ['Review:read'])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    public ?User $user = null;

    /**
     * @see https://schema.org/itemReviewed
     */
    #[ApiProperty(types: ['https://schema.org/itemReviewed'])]
    #[Assert\NotNull]
    #[Groups(groups: ['Review:read', 'Review:write'])]
    #[ORM\ManyToOne(targetEntity: Book::class)]
    #[ORM\JoinColumn(nullable: false)]
    public ?Book $book = null;

    /**
     * @see https://schema.org/datePublished
     */
    #[ApiProperty(types: ['https://schema.org/datePublished'])]
    #[Groups(groups: ['Review:read'])]
    #[ORM\Column(type: 'datetime_immutable')]
    public ?\DateTimeInterface $publishedAt = null;

    /**
     * @see https://schema.org/reviewBody
     */
    #[ApiProperty(types: ['https://schema.org/reviewBody'])]
    #[Assert\NotBlank(allowNull: false)]
    #[Groups(groups: ['Review:read', 'Review:write'])]
    #[ORM\Column(type: Types::TEXT)]
    public ?string $body = null;

    /**
     * @see https://schema.org/reviewRating
     */
    #[ApiProperty(types: ['https://schema.org/reviewRating'])]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 5)]
    #[Groups(groups: ['Review:read', 'Review:write'])]
    #[ORM\Column(type: 'smallint')]
    public ?int $rating = null;

    /**
     * @deprecated use the rating property instead
     */
    #[ApiProperty(deprecationReason: 'Use the rating property instead.')]
    #[Assert\Choice(['a', 'b', 'c', 'd'])]
    #[Groups(groups: ['Review:read', 'Review:write'])]
    #[ORM\Column(nullable: true)]
    public ?string $letter = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}
