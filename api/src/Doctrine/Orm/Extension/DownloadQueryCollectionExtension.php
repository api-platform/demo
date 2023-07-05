<?php

declare(strict_types=1);

namespace App\Doctrine\Orm\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Download;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Restrict Download collection to current user.
 */
final readonly class DownloadQueryCollectionExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private Security $security)
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if (
            Download::class !== $resourceClass
            || '_api_/downloads{._format}_get_collection' !== $operation->getName()
            || !$user = $this->security->getUser()
        ) {
            return;
        }

        $queryBuilder->andWhere(sprintf('%s.user = :user', $queryBuilder->getRootAliases()[0]))
            ->setParameter('user', $user);
    }
}
