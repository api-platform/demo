<?php

declare(strict_types=1);

namespace App\Tests\Serializer;

use App\Entity\Book;
use App\Enum\BookCondition;
use App\Repository\ReviewRepository;
use App\Serializer\BookNormalizer;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BookNormalizerTest extends TestCase
{
    private MockObject $normalizerMock;

    private MockObject $repositoryMock;

    private Stub $objectMock;

    private BookNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizerMock = $this->createMock(NormalizerInterface::class);
        $this->repositoryMock = $this->createMock(ReviewRepository::class);
        $this->objectMock = $this->createStub(Book::class);

        $this->normalizer = new BookNormalizer($this->repositoryMock);
        $this->normalizer->setNormalizer($this->normalizerMock);
    }

    #[AllowMockObjectsWithoutExpectations]
    #[Test]
    public function itDoesNotSupportInvalidObjectClass(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    #[AllowMockObjectsWithoutExpectations]
    #[Test]
    public function itDoesNotSupportInvalidContext(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization($this->objectMock, null, [BookNormalizer::class => true]));
    }

    #[AllowMockObjectsWithoutExpectations]
    #[Test]
    public function itSupportsValidObjectClassAndContext(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization($this->objectMock));
    }

    #[Test]
    public function itNormalizesData(): void
    {
        $expectedObject = $this->objectMock;
        $expectedObject->rating = 3;

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
