<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\ArchivableInterface;
use Doctrine\ORM\QueryBuilder;

final class ArchivedFilter implements FilterInterface
{
    private const PARAMETER_NAME = 'archived';

    /**
     * Filter entities that have a not null "archivedAt" field value.
     *
     * @param array<string, mixed> $context
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, array $context = []): void
    {
        if (!is_a($resourceClass, ArchivableInterface::class, true)) {
            throw new \InvalidArgumentException("Can't apply the Archived filter on a resource ({$resourceClass}) not implementing the ArchivableInterface.");
        }

        if ($this->normalizeValue($context['filters'][self::PARAMETER_NAME] ?? null)) {
            return;
        }

        $queryBuilder->andWhere(sprintf('%s.archivedAt IS NULL', $queryBuilder->getRootAliases()[0] ?? 'o'));
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getDescription(string $resourceClass): array
    {
        $description = 'Generic filter that automatically filters archived entities. One can disable this behavior by passing "true" to this parameter.';

        return [
            self::PARAMETER_NAME => [
                'property' => self::PARAMETER_NAME,
                'type' => 'bool',
                'required' => false,
                'swagger' => [
                    'description' => $description,
                    'name' => self::PARAMETER_NAME,
                    'type' => 'bool',
                ],
                'openapi' => [
                    'description' => $description,
                    'name' => self::PARAMETER_NAME,
                    'type' => 'bool',
                ],
            ],
        ];
    }

    /**
     * @param mixed $value
     */
    private function normalizeValue($value): bool
    {
        return \in_array($value, [true, 'true', '1', 1], true);
    }
}
