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

    protected function setup(): void
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
        self::assertArrayHasKey('hydra:title', $docs);
        self::assertArrayHasKey('hydra:description', $docs);
        self::assertArrayHasKey('hydra:supportedClass', $docs);

        // Get supported classes
        $supportedClasses = $docs['hydra:supportedClass'];
        self::assertIsArray($supportedClasses);

        // Find resource classes (Book, Review, Bookmark, User)
        $classNames = array_column($supportedClasses, 'hydra:title');

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

    #[Test]
    public function booksResourceHasCorrectShortNameAndTypes(): void
    {
        $response = $this->client->request('GET', '/docs.jsonld');

        self::assertResponseIsSuccessful();

        $docs = $response->toArray();
        $supportedClasses = $docs['hydra:supportedClass'];

        // Find Book class in supported classes
        $bookClass = null;
        foreach ($supportedClasses as $class) {
            if (isset($class['hydra:title']) && $class['hydra:title'] === 'Book') {
                $bookClass = $class;
                break;
            }
        }

        self::assertNotNull($bookClass, 'Book class should exist in supportedClass');

        // Verify the Book class has the correct structure
        self::assertSame('Book', $bookClass['hydra:title']);

        // The @id should reference the short name, not the full type IRI
        self::assertMatchesRegularExpression('/Book/', $bookClass['@id'], 'Book @id should reference the shortName');
        self::assertStringNotContainsString('https://schema.org/Book', $bookClass['@id'], 'Book @id should not be the full type IRI');
    }
}
