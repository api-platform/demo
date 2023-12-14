<?php

declare(strict_types=1);

namespace App\Tests\Serializer;

use App\Entity\Book;
use App\Enum\BookCondition;
use App\Repository\ReviewRepository;
use App\Serializer\BookNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;

final class BookNormalizerTest extends TestCase
{
    private MockObject|NormalizerInterface $normalizerMock;
    private MockObject|RouterInterface $routerMock;
    private MockObject|ReviewRepository $repositoryMock;
    private Book|MockObject $objectMock;
    private BookNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizerMock = $this->createMock(NormalizerInterface::class);
        $this->routerMock = $this->createMock(RouterInterface::class);
        $this->repositoryMock = $this->createMock(ReviewRepository::class);
        $this->objectMock = $this->createMock(Book::class);

        $this->normalizer = new BookNormalizer($this->routerMock, $this->repositoryMock);
        $this->normalizer->setNormalizer($this->normalizerMock);
    }

    /**
     * @test
     */
    public function itDoesNotSupportInvalidObjectClass(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    /**
     * @test
     */
    public function itDoesNotSupportInvalidContext(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization($this->objectMock, null, [BookNormalizer::class => true]));
    }

    /**
     * @test
     */
    public function itSupportsValidObjectClassAndContext(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization($this->objectMock));
    }

    /**
     * @test
     */
    public function itNormalizesData(): void
    {
        $expectedObject = $this->objectMock;
        $expectedObject->reviews = '/books/a528046c-7ba1-4acc-bff2-b5390ab17d41/reviews';
        $expectedObject->rating = 3;

        $this->objectMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(Uuid::fromString('a528046c-7ba1-4acc-bff2-b5390ab17d41'))
        ;
        $this->routerMock
            ->expects($this->once())
            ->method('generate')
            ->with('_api_/books/{bookId}/reviews{._format}_get_collection', ['bookId' => 'a528046c-7ba1-4acc-bff2-b5390ab17d41'])
            ->willReturn('/books/a528046c-7ba1-4acc-bff2-b5390ab17d41/reviews')
        ;
        $this->repositoryMock
            ->expects($this->once())
            ->method('getAverageRating')
            ->with($this->objectMock)
            ->willReturn(3)
        ;
        $this->normalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->with($expectedObject, null, [BookNormalizer::class => true])
            ->willReturn([
                'book' => 'https://openlibrary.org/books/OL2055137M.json',
                'title' => 'Foundation',
                'author' => 'Dan Simmons',
                'condition' => BookCondition::NewCondition->value,
                'reviews' => '/books/a528046c-7ba1-4acc-bff2-b5390ab17d41/reviews',
                'rating' => 3,
            ])
        ;

        $this->assertEquals([
            'book' => 'https://openlibrary.org/books/OL2055137M.json',
            'title' => 'Foundation',
            'author' => 'Dan Simmons',
            'condition' => BookCondition::NewCondition->value,
            'reviews' => '/books/a528046c-7ba1-4acc-bff2-b5390ab17d41/reviews',
            'rating' => 3,
        ], $this->normalizer->normalize($this->objectMock));
    }
}
