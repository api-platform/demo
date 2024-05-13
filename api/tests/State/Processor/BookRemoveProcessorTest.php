<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Book;
use App\State\Processor\BookRemoveProcessor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BookRemoveProcessorTest extends TestCase
{
    private MockObject|ProcessorInterface $removeProcessorMock;
    private ResourceMetadataCollection $resourceMetadataCollection;
    private Book|MockObject $objectMock;
    private MockObject|Operation $operationMock;
    private BookRemoveProcessor $processor;

    protected function setUp(): void
    {
        $this->removeProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->resourceMetadataCollection = new ResourceMetadataCollection(Book::class, [
            new ApiResource(operations: [new Get('/admin/books/{id}{._format}')]),
            new ApiResource(operations: [new Get('/books/{id}{._format}')]),
        ]);
        $this->objectMock = $this->createMock(Book::class);
        $this->operationMock = $this->createMock(Operation::class);

        $this->processor = new BookRemoveProcessor($this->removeProcessorMock);
    }

    #[Test]
    public function itRemovesBookAndSendMercureUpdates(): void
    {
        $this->removeProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->objectMock, $this->operationMock, [], [])
        ;

        $this->processor->process($this->objectMock, $this->operationMock);
    }
}
