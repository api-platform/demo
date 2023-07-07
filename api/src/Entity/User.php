<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * A person (alive, dead, undead, or fictional).
 *
 * @see https://schema.org/Person
 */
#[ORM\Entity]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    types: ['https://schema.org/Person'],
    operations: [
        new Get(
            uriTemplate: '/admin/users/{id}{._format}',
            security: 'is_granted("ROLE_ADMIN")'
        ),
        new Get(
            uriTemplate: '/users/{id}{._format}',
            security: 'is_granted("ROLE_USER") and object.getUserIdentifier() === user.getUserIdentifier()'
        ),
    ],
    normalizationContext: ['groups' => ['User:read']]
)]
#[UniqueEntity('email')]
class User implements UserInterface
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
     * @see https://schema.org/email
     */
    #[ORM\Column(unique: true)]
    public ?string $email = null;

    /**
     * @see https://schema.org/givenName
     */
    #[ORM\Column]
    #[ApiProperty(types: ['https://schema.org/givenName'])]
    #[Groups(groups: ['User:read', 'Review:read', 'Download:read:admin'])]
    public ?string $firstName = null;

    /**
     * @see https://schema.org/familyName
     */
    #[ORM\Column]
    #[ApiProperty(types: ['https://schema.org/familyName'])]
    #[Groups(groups: ['User:read', 'Review:read', 'Download:read:admin'])]
    public ?string $lastName = null;

    #[ORM\Column(type: 'json')]
    public array $roles = [];

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return array<int, string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
}
