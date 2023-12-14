<?php

declare(strict_types=1);

namespace App\Security\Core;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @implements AttributesBasedUserProviderInterface<UserInterface|User>
 */
final readonly class UserProvider implements AttributesBasedUserProviderInterface
{
    public function __construct(private ManagerRegistry $registry, private UserRepository $repository) {}

    public function refreshUser(UserInterface $user): UserInterface
    {
        $manager = $this->registry->getManagerForClass($user::class);
        if (!$manager) {
            throw new UnsupportedUserException(sprintf('User class "%s" not supported.', $user::class));
        }

        $manager->refresh($user);

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }

    /**
     * Create or update User on login.
     */
    public function loadUserByIdentifier(string $identifier, array $attributes = []): UserInterface
    {
        $user = $this->repository->findOneBy(['email' => $identifier]) ?: new User();
        $user->email = $identifier;

        if (!isset($attributes['sub'])) {
            throw new UnsupportedUserException('Property "sub" is missing in token attributes.');
        }
        try {
            $user->sub = Uuid::fromString($attributes['sub']);
        } catch (\Throwable $e) {
            throw new UnsupportedUserException($e->getMessage(), $e->getCode(), $e);
        }

        if (!isset($attributes['given_name'])) {
            throw new UnsupportedUserException('Property "given_name" is missing in token attributes.');
        }
        $user->firstName = $attributes['given_name'];

        if (!isset($attributes['family_name'])) {
            throw new UnsupportedUserException('Property "family_name" is missing in token attributes.');
        }
        $user->lastName = $attributes['family_name'];

        $this->repository->save($user);

        return $user;
    }
}
