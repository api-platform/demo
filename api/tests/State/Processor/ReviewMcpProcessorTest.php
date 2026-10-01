<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\McpTool;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Book;
use App\Entity\Review;
use App\Mcp\Input\ReviewInput;
use App\Repository\BookRepository;
use App\State\Processor\ReviewMcpProcessor;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AllowMockObjectsWithoutExpectations]
final class ReviewMcpProcessorTest extends TestCase
{
    /**
     * @var MockObject&ProcessorInterface
     */
    private MockObject $persistProcessorMock;

    /**
     * @var MockObject&BookRepository
     */
    private MockObject $bookRepositoryMock;

    /**
     * @var MockObject&ValidatorInterface
     */
    private MockObject $validatorMock;

    private ReviewMcpProcessor $processor;

    private ReviewInput $data;

    protected function setUp(): void
    {
        $this->persistProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->bookRepositoryMock = $this->createMock(BookRepository::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->processor = new ReviewMcpProcessor(
            $this->persistProcessorMock,
            $this->bookRepositoryMock,
            $this->validatorMock
        );

        $this->data = new ReviewInput();
        $this->data->bookId = (string) Uuid::v7();
        $this->data->body = 'Very good book!';
        $this->data->rating = 5;
    }

    #[Test]
    public function itCreatesAReviewFromToolArguments(): void
    {
        $book = $this->createStub(Book::class);
        $operation = new McpTool();
        $expectedReview = new Review();

        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->with($this->data->bookId)
            ->willReturn($book)
        ;
        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($this->callback(static fn (Review $review): bool => $book === $review->book
                && 'Very good book!' === $review->body
                && 5 === $review->rating), null, ['Default', 'Review:create'])
            ->willReturn(new ConstraintViolationList())
        ;
        $this->persistProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->isInstanceOf(Review::class), $operation, [], [])
            ->willReturn($expectedReview)
        ;

        $this->assertSame($expectedReview, $this->processor->process($this->data, $operation));
    }

    #[Test]
    public function itCannotCreateAReviewOnAnInvalidBookIdentifier(): void
    {
        $this->data->bookId = 'invalid';

        $this->bookRepositoryMock->expects($this->never())->method('find');
        $this->persistProcessorMock->expects($this->never())->method('process');

        $this->expectException(NotFoundHttpException::class);

        $this->processor->process($this->data, new McpTool());
    }

    #[Test]
    public function itCannotCreateAReviewOnAnUnknownBook(): void
    {
        $this->bookRepositoryMock->expects($this->once())->method('find')->willReturn(null);
        $this->persistProcessorMock->expects($this->never())->method('process');

        $this->expectException(NotFoundHttpException::class);

        $this->processor->process($this->data, new McpTool());
    }

    #[Test]
    public function itCannotCreateAnInvalidReview(): void
    {
        $this->bookRepositoryMock->method('find')->willReturn($this->createStub(Book::class));
        $this->validatorMock
            ->method('validate')
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('You have already reviewed this book.', null, [], null, '', null),
            ]))
        ;
        $this->persistProcessorMock->expects($this->never())->method('process');

        $this->expectException(ValidationException::class);

        $this->processor->process($this->data, new McpTool());
    }
}
