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
     * @param array<string, mixed> $context
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, array $context = []): void
    {
        if (!is_a($resourceClass, ArchivableInterface::class, true)) {
            throw new \InvalidArgumentException("Can't apply the Archived filter on a resource ({$resourceClass}) not implementing the ArchivableInterface.");
        }

        // Parameter not provided or not supported
        $archivedValue = $this->normalizeValue($context['filters'][self::PARAMETER_NAME] ?? null);
        if (null === $archivedValue) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0] ?? 'o';
        if ($archivedValue) {
            $queryBuilder->andWhere(sprintf('%s.archivedAt IS NOT NULL', $alias));
        } else {
            $queryBuilder->andWhere(sprintf('%s.archivedAt IS NULL', $alias));
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getDescription(string $resourceClass): array
    {
        $description = 'Filter archived entities. "true" or "1" returns archived only. "false" or "0" returns not archived only.';

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

    private function normalizeValue(mixed $value): ?bool
    {
        if (\in_array($value, [false, 'false', '0', 0], true)) {
            return false;
        }

        if (\in_array($value, [true, 'true', '1', 1], true)) {
            return true;
        }

        return null;
    }
}
