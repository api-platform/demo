<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class ArchivedFilter extends AbstractFilter
{
    private const PARAMETER_NAME = 'archived';

    /**
     * Filter entities that have a not null "archivedAt" field value.
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, array $context = []): void
    {
        $archived = $context['filters'][self::PARAMETER_NAME] ?? null;
        if ($archived !== 'true') {
            $queryBuilder->andWhere(sprintf('%s.archivedAt IS NULL', $queryBuilder->getRootAliases()[0] ?? 'o'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        $description = 'Generic filter that automatically filters archived entities. One can disable this behavior by passing "true" to this parameter.';
        $type = 'string';

        return [
            self::PARAMETER_NAME => [
                'property' => 'hydra:freetextQuery',
                'type' => $type,
                'required' => false,
                'swagger' => [
                    'description' => $description,
                    'name' => self::PARAMETER_NAME,
                    'type' => $type,
                ],
                'openapi' => [
                    'description' => $description,
                    'name' => self::PARAMETER_NAME,
                    'type' => $type,
                ],
            ],
        ];
    }

    /**
     * This is a generic filter that works on a unique property so we don't have
     * to filter by property.
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
    }
}
