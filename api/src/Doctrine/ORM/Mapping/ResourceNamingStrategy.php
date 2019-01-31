<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Mapping;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\NamingStrategy as NamingStrategyInterface;

class ResourceNamingStrategy implements NamingStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function classToTableName($className): string
    {
        return Inflector::pluralize(Inflector::tableize(self::shortName($className)));
    }

    /**
     * {@inheritdoc}
     */
    public function propertyToColumnName($propertyName, $className = null): string
    {
        return Inflector::tableize($propertyName);
    }

    /**
     * {@inheritdoc}
     */
    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null): string
    {
        return Inflector::tableize($propertyName).'_'.$embeddedColumnName;
    }

    /**
     * {@inheritdoc}
     */
    public function referenceColumnName(): string
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function joinColumnName($propertyName): string
    {
        return Inflector::tableize($propertyName).'_'.$this->referenceColumnName();
    }

    /**
     * {@inheritdoc}
     */
    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null): string
    {
        return $this->classToTableName($sourceEntity).'_'.(null !== $propertyName ? $this->propertyToColumnName($propertyName) : $this->classToTableName($targetEntity));
    }

    /**
     * {@inheritdoc}
     */
    public function joinKeyColumnName($entityName, $referencedColumnName = null): string
    {
        return Inflector::tableize(self::shortName($entityName)).'_'.($referencedColumnName ?? $this->referenceColumnName());
    }

    private static function shortName(string $className): string
    {
        if (false !== \strpos($className, '\\')) {
            $className = \substr($className, \strrpos($className, '\\') + 1);
        }

        return $className;
    }
}
