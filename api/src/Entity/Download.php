<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Serializer\IriTransformerNormalizer;
use App\State\Processor\DownloadPersistProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The act of downloading an object.
 *
 * @see https://schema.org/DownloadAction
 */
#[ORM\Entity]
#[ApiResource(
    types: ['https://schema.org/DownloadAction'],
    operations: [
        new GetCollection(
            uriTemplate: '/admin/downloads{._format}',
            itemUriTemplate: '/admin/downloads/{id}{._format}',
            security: 'is_granted("ROLE_ADMIN")',
            normalizationContext: [
                'groups' => ['Download:read', 'Download:read:admin'],
                IriTransformerNormalizer::CONTEXT_KEY => [
                    'book' => '/admin/books/{id}{._format}',
                    'user' => '/admin/users/{id}{._format}',
                ],
            ],
        ),
        new Get(
            uriTemplate: '/admin/downloads/{id}{._format}',
            security: 'is_granted("ROLE_ADMIN")',
            normalizationContext: [
                'groups' => ['Download:read', 'Download:read:admin'],
                IriTransformerNormalizer::CONTEXT_KEY => [
                    'book' => '/admin/books/{id}{._format}',
                    'user' => '/admin/users/{id}{._format}',
                ],
            ],
        ),
        new GetCollection(
            filters: [], // disable filters
            itemUriTemplate: '/downloads/{id}{._format}'
        ),
        new Get(),
        new Post(
            processor: DownloadPersistProcessor::class,
            itemUriTemplate: '/downloads/{id}{._format}'
        ),
    ],
    normalizationContext: [
        'groups' => ['Download:read'],
        IriTransformerNormalizer::CONTEXT_KEY => [
            'book' => '/books/{id}{._format}',
        ],
    ],
    denormalizationContext: ['groups' => ['Download:write']],
    mercure: true,
    security: 'is_granted("ROLE_USER")'
)]
class Download
{
    /**
     * @see https://schema.org/identifier
     */
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ApiProperty(identifier: true, types: ['https://schema.org/identifier'])]
    private ?Uuid $id = null;

    /**
     * @see https://schema.org/agent
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[ApiProperty(types: ['https://schema.org/agent'])]
    #[Groups(groups: ['Download:read:admin'])]
    public ?User $user = null;

    /**
     * @see https://schema.org/object
     */
    #[ORM\ManyToOne(targetEntity: Book::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[ApiProperty(types: ['https://schema.org/object'])]
    #[Groups(groups: ['Download:read', 'Download:write'])]
    #[Assert\NotNull]
    public ?Book $book = null;

    /**
     * @see https://schema.org/startTime
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[ApiProperty(types: ['https://schema.org/startTime'])]
    #[Groups(groups: ['Download:read'])]
    public ?\DateTimeInterface $downloadedAt = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}
