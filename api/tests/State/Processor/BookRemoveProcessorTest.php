<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Book;
use App\State\Processor\BookRemoveProcessor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BookRemoveProcessorTest extends TestCase
{
    private MockObject $removeProcessorMock;

    private MockObject $objectMock;

    private MockObject $operationMock;

    private BookRemoveProcessor $processor;

    protected function setUp(): void
    {
        $this->removeProcessorMock = $this->createMock(ProcessorInterface::class);
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
