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
