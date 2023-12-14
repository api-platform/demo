<?php

declare(strict_types=1);

namespace App\Doctrine\Orm\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\PropertyHelperTrait;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

final class NameFilter extends AbstractFilter
{
    use PropertyHelperTrait;

    public function getDescription(string $resourceClass): array
    {
        return [
            'name' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'strategy' => 'ipartial',
                'is_collection' => false,
            ],
        ];
    }

    /**
     * @param string|null $value
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if ('name' !== $property) {
            return;
        }

        $values = $this->normalizeValues($value, $property);
        if (null === $values) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $expressions = [];
        foreach ($values as $key => $value) {
            $parameterName = $queryNameGenerator->generateParameterName("name{$key}");
            $queryBuilder->setParameter($parameterName, "%{$value}%");
            $expressions[] = $queryBuilder->expr()->orX(
                $queryBuilder->expr()->like(sprintf('%s.firstName', $alias), ":{$parameterName}"),
                $queryBuilder->expr()->like(sprintf('%s.lastName', $alias), ":{$parameterName}")
            );
        }
        $queryBuilder->andWhere($queryBuilder->expr()->andX(...$expressions));
    }

    /**
     * @param string|null $value
     */
    protected function normalizeValues($value, string $property): ?array
    {
        if (!\is_string($value) || empty(trim($value))) {
            return null;
        }

        $values = explode(' ', $value);
        foreach ($values as $key => $value) {
            if (empty(trim($value))) {
                unset($values[$key]);
            }
        }

        if (empty($values)) {
            $this->getLogger()->notice('Invalid filter ignored', [
                'exception' => new InvalidArgumentException(sprintf('At least one value is required, multiple values should be in "%1$s[]=firstvalue&%1$s[]=secondvalue" format', $property)),
            ]);

            return null;
        }

        return array_values($values);
    }
}
