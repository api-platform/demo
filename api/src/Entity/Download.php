<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
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
    shortName: 'Download',
    types: ['https://schema.org/DownloadAction'],
    operations: [
        new GetCollection(security: 'is_granted("ROLE_USER")'),
        new GetCollection(uriTemplate: '/admin/downloads.{_format}', security: 'is_granted("ROLE_ADMIN")'),
        new Post(security: 'is_granted("ROLE_USER")'),
    ],
    normalizationContext: ['groups' => ['Download:read']],
    denormalizationContext: ['groups' => ['Download:write']],
    mercure: true
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
    #[ApiProperty(types: ['https://schema.org/identifier'])]
    private ?Uuid $id = null;

    /**
     * @see https://schema.org/agent
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(types: ['https://schema.org/agent'])]
    #[Groups(groups: ['Download:read'])]
    public ?User $user = null;

    /**
     * @see https://schema.org/object
     */
    #[ORM\ManyToOne(targetEntity: Book::class)]
    #[ORM\JoinColumn(nullable: false)]
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
