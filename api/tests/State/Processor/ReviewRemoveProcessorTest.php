<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Review;
use App\Entity\User;
use App\Security\Http\Protection\ResourceHandlerInterface;
use App\State\Processor\ReviewRemoveProcessor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ReviewRemoveProcessorTest extends TestCase
{
    private MockObject $removeProcessorMock;

    private MockObject $objectMock;

    private MockObject $operationMock;

    private MockObject $resourceHandlerMock;

    private ReviewRemoveProcessor $processor;

    protected function setUp(): void
    {
        $this->removeProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->resourceHandlerMock = $this->createMock(ResourceHandlerInterface::class);
        $this->objectMock = $this->createMock(Review::class);
        $this->operationMock = $this->createMock(Operation::class);

        $this->processor = new ReviewRemoveProcessor(
            $this->removeProcessorMock,
            $this->resourceHandlerMock
        );
    }

    #[Test]
    public function itRemovesBookAndSendMercureUpdates(): void
    {
        $this->removeProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->objectMock, $this->operationMock, [], [])
        ;
        $this->objectMock->user = $this->createMock(User::class);
        $this->objectMock->user->email = 'john.doe@example.com';
        $this->resourceHandlerMock
            ->expects($this->once())
            ->method('delete')
            ->with($this->objectMock, $this->objectMock->user, [
                'operation_name' => '/books/{bookId}/reviews/{id}{._format}',
            ])
        ;

        $this->processor->process($this->objectMock, $this->operationMock);
    }
}
