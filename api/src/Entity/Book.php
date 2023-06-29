<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Enum\BookCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A book.
 *
 * @see https://schema.org/Book
 */
#[ApiResource(
    shortName: 'Book',
    types: ['https://schema.org/Book', 'https://schema.org/Offer'],
    operations: [
        new GetCollection(),
        new Get(),
        new Post(routePrefix: '/admin', security: 'is_granted("ROLE_ADMIN")'),
        new Patch(routePrefix: '/admin', security: 'is_granted("ROLE_ADMIN")'),
        new Delete(routePrefix: '/admin', security: 'is_granted("ROLE_ADMIN")'),
    ],
    normalizationContext: ['groups' => ['Book:read', 'Enum:read']],
    denormalizationContext: ['groups' => ['Book:write']],
    mercure: true
)]
#[ORM\Entity]
#[UniqueEntity(fields: ['book'])]
class Book
{
    /**
     * @see https://schema.org/identifier
     */
    #[ApiProperty(identifier: true, types: ['https://schema.org/identifier'])]
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    /**
     * @see https://schema.org/itemOffered
     */
    #[ApiProperty(types: ['https://schema.org/itemOffered', 'https://purl.org/dc/terms/BibliographicResource'])]
    #[Groups(groups: ['Book:read', 'Book:write'])]
    #[Assert\NotBlank(allowNull: false)]
    #[Assert\Url]
    #[ORM\Column(unique: true)]
    public ?string $book = null;

    /**
     * @see https://schema.org/headline
     */
    #[ApiProperty(types: ['https://schema.org/headline'])]
    #[Groups(groups: ['Book:read'])]
    #[ORM\Column]
    public ?string $title = null;

    /**
     * @see https://schema.org/author
     */
    #[ApiProperty(types: ['https://schema.org/author'])]
    #[Groups(groups: ['Book:read'])]
    #[ORM\Column]
    public ?string $author = null;

    /**
     * @see https://schema.org/OfferItemCondition
     */
    #[ApiProperty(types: ['https://schema.org/OfferItemCondition'])]
    #[Groups(groups: ['Book:read', 'Book:write'])]
    #[Assert\NotNull]
    #[ORM\Column(name: '`condition`', type: 'string', enumType: BookCondition::class)]
    public ?BookCondition $condition = null;

    /**
     * An IRI of reviews
     *
     * @see https://schema.org/reviews
     */
    #[ApiProperty(types: ['https://schema.org/reviews'])]
    #[Groups(groups: ['Book:read'])]
    public ?string $reviews = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}
