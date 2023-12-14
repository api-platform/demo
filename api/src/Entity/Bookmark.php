<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\BookmarkRepository;
use App\Serializer\IriTransformerNormalizer;
use App\State\Processor\BookmarkPersistProcessor;
use App\Validator\UniqueUserBook;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An agent bookmarks/flags/labels/tags/marks an object.
 *
 * @see https://schema.org/BookmarkAction
 */
#[ApiResource(
    types: ['https://schema.org/BookmarkAction'],
    order: ['bookmarkedAt' => 'DESC'],
    operations: [
        new GetCollection(),
        new Delete(
            security: 'is_granted("ROLE_USER") and object.user === user'
        ),
        new Post(
            processor: BookmarkPersistProcessor::class
        ),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Bookmark:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
        IriTransformerNormalizer::CONTEXT_KEY => [
            'book' => '/books/{id}{._format}',
        ],
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Bookmark:write'],
    ],
    collectDenormalizationErrors: true,
    mercure: true,
    security: 'is_granted("ROLE_USER")'
)]
#[ORM\Entity(repositoryClass: BookmarkRepository::class)]
#[ORM\UniqueConstraint(fields: ['user', 'book'])]
#[UniqueUserBook(message: 'You have already bookmarked this book.')]
class Bookmark
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
     * @see https://schema.org/agent
     */
    #[ApiProperty(types: ['https://schema.org/agent'])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    public ?User $user = null;

    /**
     * @see https://schema.org/object
     */
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[ApiProperty(types: ['https://schema.org/object'])]
    #[Assert\NotNull]
    #[Groups(groups: ['Bookmark:read', 'Bookmark:write'])]
    #[ORM\ManyToOne(targetEntity: Book::class)]
    #[ORM\JoinColumn(nullable: false)]
    public ?Book $book = null;

    /**
     * @see https://schema.org/startTime
     */
    #[ApiProperty(types: ['https://schema.org/startTime'])]
    #[Groups(groups: ['Bookmark:read'])]
    #[ORM\Column(type: 'datetime_immutable')]
    public ?\DateTimeInterface $bookmarkedAt = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}
