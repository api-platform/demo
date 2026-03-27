<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use PHPUnit\Framework\Attributes\Test;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class HydraDocumentationTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    #[Test]
    public function hydraDocumentationIsValid(): void
    {
        $response = $this->client->request('GET', '/docs.jsonld');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $docs = $response->toArray();

        // Check that the documentation contains the expected structure
        self::assertArrayHasKey('@context', $docs);
        self::assertArrayHasKey('title', $docs);
        self::assertArrayHasKey('description', $docs);
        self::assertArrayHasKey('supportedClass', $docs);

        // Get supported classes
        $supportedClasses = $docs['supportedClass'];
        self::assertIsArray($supportedClasses);

        // Find resource classes (Book, Review, Bookmark, User)
        $classNames = array_column($supportedClasses, 'title');

        // Verify that resources use shortNames, not full type IRIs
        self::assertContains('Book', $classNames, 'Book resource should be identified by shortName "Book"');
        self::assertContains('Review', $classNames, 'Review resource should be identified by shortName "Review"');
        self::assertContains('Bookmark', $classNames, 'Bookmark resource should be identified by shortName "Bookmark"');
        self::assertContains('User', $classNames, 'User resource should be identified by shortName "User"');

        // Verify that resources DON'T use full type IRIs as class names
        self::assertNotContains('https://schema.org/Book', $classNames, 'Book should not use full IRI as class name');
        self::assertNotContains('https://schema.org/Review', $classNames, 'Review should not use full IRI as class name');
        self::assertNotContains('https://schema.org/BookmarkAction', $classNames, 'Bookmark should not use full IRI as class name');
        self::assertNotContains('https://schema.org/Person', $classNames, 'User should not use full IRI as class name');
    }

    /**
     * Regression test for api-platform/hydra DocumentationNormalizer bug.
     *
     * When `serializer.hydra_prefix` is false (the default since API Platform 4.x), the
     * `DocumentationNormalizer` emits:
     *
     *   "owl:onProperty": {"@id": "member"}
     *
     * instead of:
     *
     *   "owl:onProperty": {"@id": "hydra:member"}
     *
     * The bare term `"member"` cannot be expanded by jsonld.js to
     * `http://www.w3.org/ns/hydra/core#member` because `@id` values are expanded
     * with `vocab=false`, which skips term definitions. Only compact IRIs (prefix:localname)
     * are always resolved. This causes `@api-platform/api-doc-parser`'s `findRelatedClass`
     * to throw "Cannot find the class related to …#Entrypoint/book", breaking HydraAdmin.
     *
     * @see https://github.com/api-platform/hydra/blob/main/src/Serializer/DocumentationNormalizer.php
     */
    #[Test]
    public function entrypointOwlOnPropertyIsMemberCompactIriNotBareTerm(): void
    {
        $response = $this->client->request('GET', '/docs.jsonld');

        self::assertResponseIsSuccessful();

        $docs = $response->toArray();

        // Find the Entrypoint class in supportedClass
        $entrypointClass = null;
        foreach ($docs['supportedClass'] as $class) {
            if ('#Entrypoint' === ($class['@id'] ?? null)) {
                $entrypointClass = $class;
                break;
            }
        }

        self::assertNotNull($entrypointClass, 'Entrypoint class should exist in supportedClass');

        $supportedProperties = $entrypointClass['supportedProperty'] ?? [];
        self::assertNotEmpty($supportedProperties, 'Entrypoint should have supported properties');

        foreach ($supportedProperties as $supportedProperty) {
            $property = $supportedProperty['property'] ?? null;
            if (null === $property) {
                continue;
            }

            foreach ($property['range'] ?? [] as $rangeEntry) {
                $onPropertyId = $rangeEntry['owl:equivalentClass']['owl:onProperty']['@id'] ?? null;
                if (null === $onPropertyId) {
                    continue;
                }

                // A bare "member" cannot be expanded to http://www.w3.org/ns/hydra/core#member
                // by jsonld.js because @id values are resolved with vocab=false (term definitions
                // are ignored). Only compact IRIs like "hydra:member" or the full IRI work.
                // Fix in DocumentationNormalizer: replace `$hydraPrefix.'member'` with `'hydra:member'`
                // (or ContextBuilderInterface::HYDRA_NS.'member') for owl:onProperty.
                self::assertNotSame(
                    'member',
                    $onPropertyId,
                    \sprintf(
                        'owl:onProperty @id for entrypoint property "%s" is the bare term "member". '
                        . 'jsonld.js cannot expand it to http://www.w3.org/ns/hydra/core#member '
                        . '(@id values use vocab=false, which skips term definitions). '
                        . 'Use "hydra:member" instead.',
                        $property['@id'] ?? '?'
                    )
                );
            }
        }
    }

    #[Test]
    public function booksResourceHasCorrectShortNameAndTypes(): void
    {
        $response = $this->client->request('GET', '/docs.jsonld');

        self::assertResponseIsSuccessful();

        $docs = $response->toArray();
        $supportedClasses = $docs['supportedClass'];

        // Find Book class in supported classes
        $bookClass = null;
        foreach ($supportedClasses as $class) {
            if (isset($class['title']) && 'Book' === $class['title']) {
                $bookClass = $class;
                break;
            }
        }

        self::assertNotNull($bookClass, 'Book class should exist in supportedClass');

        // Verify the Book class has the correct structure
        self::assertSame('Book', $bookClass['title']);

        // The @id should be a fragment IRI based on the shortName, not the full type IRI
        self::assertNotEquals('https://schema.org/Book', $bookClass['@id'], 'Book @id should not be the full type IRI');
        self::assertEquals('#Book', $bookClass['@id'], 'Book @id should be a fragment IRI based on the shortName');
    }
}
