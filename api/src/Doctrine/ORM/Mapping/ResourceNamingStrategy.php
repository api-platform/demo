<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Mapping;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\Mapping\NamingStrategy as NamingStrategyInterface;

class ResourceNamingStrategy implements NamingStrategyInterface
{
    private Inflector $inflector;

    public function __construct()
    {
        $this->inflector = InflectorFactory::create()->build();
    }

    /**
     * {@inheritdoc}
     */
    public function classToTableName($className): string
    {
        return $this->getInflector()->pluralize($this->getInflector()->tableize(self::shortName($className)));
    }

    /**
     * {@inheritdoc}
     */
    public function propertyToColumnName($propertyName, $className = null): string
    {
        return $this->getInflector()->tableize($propertyName);
    }

    /**
     * {@inheritdoc}
     */
    public function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null): string
    {
        return $this->getInflector()->tableize($propertyName).'_'.$embeddedColumnName;
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
        return $this->getInflector()->tableize($propertyName).'_'.$this->referenceColumnName();
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
        return $this->getInflector()->tableize(self::shortName($entityName)).'_'.($referencedColumnName ?? $this->referenceColumnName());
    }

    private static function shortName(string $className): string
    {
        if (\str_contains($className, '\\')) {
            $className = substr($className, strrpos($className, '\\') + 1);
        }

        return $className;
    }

    private function getInflector(): Inflector
    {
        return $this->inflector;
    }
}
