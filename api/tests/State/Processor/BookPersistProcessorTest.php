<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BookRepository\BookRepositoryInterface;
use App\Entity\Book;
use App\State\Processor\BookPersistProcessor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BookPersistProcessorTest extends TestCase
{
    private MockObject|ProcessorInterface $persistProcessorMock;
    private MockObject|BookRepositoryInterface $bookRepositoryMock;
    private Book|MockObject $objectMock;
    private MockObject|Operation $operationMock;
    private BookPersistProcessor $processor;

    protected function setUp(): void
    {
        $this->persistProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->bookRepositoryMock = $this->createMock(BookRepositoryInterface::class);
        $this->objectMock = $this->createMock(Book::class);
        $this->objectMock->book = 'https://openlibrary.org/books/OL2055137M.json';
        $this->operationMock = $this->createMock(Operation::class);

        $this->processor = new BookPersistProcessor(
            $this->persistProcessorMock,
            $this->bookRepositoryMock
        );
    }

    #[Test]
    public function itUpdatesBookDataBeforeSaveAndSendMercureUpdates(): void
    {
        $expectedData = $this->objectMock;
        $expectedData->title = 'Foundation';
        $expectedData->author = 'Dan Simmons';

        $this->bookRepositoryMock
            ->expects($this->once())
            ->method('find')
            ->willReturn($expectedData)
        ;
        $this->persistProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($expectedData, $this->operationMock, [], [])
            ->willReturn($expectedData)
        ;

        $this->assertEquals($expectedData, $this->processor->process($this->objectMock, $this->operationMock));
    }
}
