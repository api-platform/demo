<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Book;
use App\State\Processor\BookRemoveProcessor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class BookRemoveProcessorTest extends TestCase
{
    private MockObject $removeProcessorMock;

    private Stub $objectMock;

    private Stub $operationMock;

    private BookRemoveProcessor $processor;

    protected function setUp(): void
    {
        $this->removeProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->objectMock = $this->createStub(Book::class);
        $this->operationMock = $this->createStub(Operation::class);

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
