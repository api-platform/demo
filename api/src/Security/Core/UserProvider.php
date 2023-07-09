<?php

declare(strict_types=1);

namespace App\Security\Core;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\AttributesBasedUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class UserProvider implements AttributesBasedUserProviderInterface
{
    public function __construct(private ManagerRegistry $registry, private UserRepository $repository)
    {
    }

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

        if (!isset($attributes['firstName'])) {
            throw new UnsupportedUserException('Property "firstName" is missing in token attributes.');
        }
        $user->firstName = $attributes['firstName'];

        if (!isset($attributes['lastName'])) {
            throw new UnsupportedUserException('Property "lastName" is missing in token attributes.');
        }
        $user->lastName = $attributes['lastName'];

        $this->repository->save($user);

        return $user;
    }
}
