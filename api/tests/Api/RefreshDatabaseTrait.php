<?php

declare(strict_types=1);

namespace App\Tests\Api;

use InvalidArgumentException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait as HautelookRefreshDatabaseTrait;

/**
 * Create the schema if necessary, then populate the DB.
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
trait RefreshDatabaseTrait
{
    // This trait provided by HautelookAliceBundle will take care of refreshing
    // the database content to put it in a known state between every tests
    use HautelookRefreshDatabaseTrait {
        populateDatabase as protected populateDB;
    }

    protected static function populateDatabase(): void
    {
        self::buildSchema();
        self::populateDB();
    }

    protected static function buildSchema(): void
    {
        $em = static::getContainer()->get('doctrine')->getManager(static::$manager);
        $meta = $em->getMetadataFactory()->getAllMetadata();

        if (!empty($meta)) {
            $tool = new SchemaTool($em);
            $tool->dropSchema($meta);
            try {
                $tool->createSchema($meta);
            } catch (ToolsException $toolsException) {
                throw new InvalidArgumentException(sprintf('Database schema is not buildable: %s', $toolsException->getMessage()), $toolsException->getCode(), $toolsException);
            }
        }
    }
}
