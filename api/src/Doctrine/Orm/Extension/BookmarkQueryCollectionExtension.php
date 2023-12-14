<?php

declare(strict_types=1);

namespace App\Doctrine\Orm\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Bookmark;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Restrict Bookmark collection to current user.
 */
final readonly class BookmarkQueryCollectionExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private Security $security) {}

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if (
            Bookmark::class !== $resourceClass
            || '_api_/bookmarks{._format}_get_collection' !== $operation->getName()
            || !$user = $this->security->getUser()
        ) {
            return;
        }

        $queryBuilder
            ->andWhere(sprintf('%s.user = :user', $queryBuilder->getRootAliases()[0]))
            ->setParameter('user', $user)
        ;
    }
}
