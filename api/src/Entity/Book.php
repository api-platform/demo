<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Enum\BookCondition;
use App\Repository\BookRepository;
use App\State\Processor\BookPersistProcessor;
use App\State\Processor\BookRemoveProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A book.
 *
 * @see https://schema.org/Book
 */
#[ApiResource(
    uriTemplate: '/admin/books{._format}',
    types: ['https://schema.org/Book', 'https://schema.org/Offer'],
    operations: [
        new GetCollection(
            itemUriTemplate: '/admin/books/{id}{._format}',
            paginationClientItemsPerPage: true
        ),
        new Post(
            // Mercure publish is done manually in MercureProcessor through BookPersistProcessor
            processor: BookPersistProcessor::class,
            itemUriTemplate: '/admin/books/{id}{._format}'
        ),
        new Get(
            uriTemplate: '/admin/books/{id}{._format}'
        ),
        // https://github.com/api-platform/admin/issues/370
        new Put(
            uriTemplate: '/admin/books/{id}{._format}',
            // Mercure publish is done manually in MercureProcessor through BookPersistProcessor
            processor: BookPersistProcessor::class
        ),
        new Delete(
            uriTemplate: '/admin/books/{id}{._format}',
            // Mercure publish is done manually in MercureProcessor through BookRemoveProcessor
            processor: BookRemoveProcessor::class
        ),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Book:read:admin', 'Enum:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Book:write'],
    ],
    collectDenormalizationErrors: true,
    security: 'is_granted("ROLE_ADMIN")'
)]
#[ApiResource(
    types: ['https://schema.org/Book', 'https://schema.org/Offer'],
    operations: [
        new GetCollection(
            itemUriTemplate: '/books/{id}{._format}'
        ),
        new Get(),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Book:read', 'Enum:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ]
)]
#[ORM\Entity(repositoryClass: BookRepository::class)]
#[UniqueEntity(fields: ['book'])]
class Book
{
    /**
     * @see https://schema.org/identifier
     */
    #[ApiProperty(identifier: true, types: ['https://schema.org/identifier'])]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    private ?Uuid $id = null;

    /**
     * @see https://schema.org/itemOffered
     */
    #[ApiProperty(
        types: ['https://schema.org/itemOffered', 'https://purl.org/dc/terms/BibliographicResource'],
        example: 'https://openlibrary.org/books/OL2055137M.json'
    )]
    #[Assert\NotBlank(allowNull: false)]
    #[Assert\Url(protocols: ['https'])]
    #[Assert\Regex(pattern: '/^https:\/\/openlibrary.org\/books\/OL\d+[A-Z]{1}\.json$/')]
    #[Groups(groups: ['Book:read', 'Book:read:admin', 'Bookmark:read', 'Book:write'])]
    #[ORM\Column(unique: true)]
    public ?string $book = null;

    /**
     * @see https://schema.org/name
     */
    #[ApiFilter(OrderFilter::class)]
    #[ApiFilter(SearchFilter::class, strategy: 'i' . SearchFilterInterface::STRATEGY_PARTIAL)]
    #[ApiProperty(
        types: ['https://schema.org/name'],
        example: 'Hyperion'
    )]
    #[Groups(groups: ['Book:read', 'Book:read:admin', 'Bookmark:read', 'Review:read:admin'])]
    #[ORM\Column(type: Types::TEXT)]
    public ?string $title = null;

    /**
     * @see https://schema.org/author
     */
    #[ApiFilter(SearchFilter::class, strategy: 'i' . SearchFilterInterface::STRATEGY_PARTIAL)]
    #[ApiProperty(
        types: ['https://schema.org/author'],
        example: 'Dan Simmons'
    )]
    #[Groups(groups: ['Book:read', 'Book:read:admin', 'Bookmark:read', 'Review:read:admin'])]
    #[ORM\Column(nullable: true)]
    public ?string $author = null;

    /**
     * @see https://schema.org/OfferItemCondition
     */
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[ApiProperty(
        types: ['https://schema.org/OfferItemCondition'],
        example: BookCondition::NewCondition->value
    )]
    #[Assert\NotNull]
    #[Groups(groups: ['Book:read', 'Book:read:admin', 'Bookmark:read', 'Book:write'])]
    #[ORM\Column(name: '`condition`', type: 'string', enumType: BookCondition::class)]
    public ?BookCondition $condition = null;

    /**
     * An IRI of reviews.
     *
     * @see https://schema.org/reviews
     */
    #[ApiProperty(
        types: ['https://schema.org/reviews'],
        example: '/books/6acacc80-8321-4d83-9b02-7f2c7bf6eb1d/reviews'
    )]
    #[Groups(groups: ['Book:read', 'Bookmark:read'])]
    public ?string $reviews = null;

    /**
     * The overall rating, based on a collection of reviews or ratings, of the item.
     *
     * @see https://schema.org/aggregateRating
     */
    #[ApiProperty(
        types: ['https://schema.org/aggregateRating'],
        example: 1
    )]
    #[Groups(groups: ['Book:read', 'Book:read:admin', 'Bookmark:read'])]
    public ?int $rating = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}
