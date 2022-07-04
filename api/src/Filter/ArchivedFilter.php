<?php

declare(strict_types=1);

namespace App\Filter;

use InvalidArgumentException;
use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\ArchivableInterface;
use Doctrine\ORM\QueryBuilder;

final class ArchivedFilter implements FilterInterface
{
    /**
     * @var string
     */
    private const PARAMETER_NAME = 'archived';

    /**
     * @param array<string, mixed> $context
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if (!is_a($resourceClass, ArchivableInterface::class, true)) {
            throw new InvalidArgumentException(sprintf("Can't apply the Archived filter on a resource (%s) not implementing the ArchivableInterface.", $resourceClass));
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
     * @return array{archived: array{property: string, type: string, required: false, swagger: array{description: string, name: string, type: string}, openapi: array{description: string, name: string, type: string}}}
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
