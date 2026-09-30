<?php

declare(strict_types=1);

namespace App\Doctrine\Orm\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\OpenApiParameterFilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;
use ApiPlatform\OpenApi\Model\Parameter as OpenApiParameter;
use Doctrine\ORM\QueryBuilder;

/**
 * "name" is not a property, it's only a method "getName".
 * Can't use {@see ExactFilter}, so declare custom filter.
 */
final class NameFilter implements FilterInterface, OpenApiParameterFilterInterface
{
    #[\Override]
    public function getOpenApiParameters(Parameter $parameter): OpenApiParameter
    {
        return new OpenApiParameter(
            name: 'name',
            in: 'query',
            schema: [
                'type' => 'string',
            ],
        );
    }

    #[\Override]
    public function getDescription(string $resourceClass): array
    {
        return [];
    }

    #[\Override]
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        /** @var Parameter $parameter */
        $parameter = $context['parameter'];
        $value = $parameter->getValue();
        $property = $parameter->getProperty();

        if ('name' !== $property) {
            return;
        }

        $values = $this->normalizeValues($value);
        if (null === $values) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $expressions = [];
        foreach ($values as $key => $value) {
            $parameterName = $queryNameGenerator->generateParameterName('name' . $key);
            $queryBuilder->setParameter($parameterName, \sprintf('%%%s%%', $value));
            $expressions[] = $queryBuilder->expr()->orX(
                $queryBuilder->expr()->like(\sprintf('%s.firstName', $alias), ':' . $parameterName),
                $queryBuilder->expr()->like(\sprintf('%s.lastName', $alias), ':' . $parameterName)
            );
        }

        $queryBuilder->andWhere($queryBuilder->expr()->andX(...$expressions));
    }

    /**
     * @param string|null $value
     */
    private function normalizeValues($value): ?array
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

        return empty($values) ? null : array_values($values);
    }
}
