<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
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
    private MockObject|ProcessorInterface $removeProcessorMock;
    private ResourceMetadataCollection $resourceMetadataCollection;
    private MockObject|Review $objectMock;
    private MockObject|Operation $operationMock;
    private ResourceHandlerInterface|MockObject $resourceHandlerMock;
    private ReviewRemoveProcessor $processor;

    protected function setUp(): void
    {
        $this->removeProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->resourceHandlerMock = $this->createMock(ResourceHandlerInterface::class);
        $this->resourceMetadataCollection = new ResourceMetadataCollection(Review::class, [
            new ApiResource(operations: [new Get('/admin/reviews/{id}{._format}')]),
            new ApiResource(operations: [new Get('/books/{bookId}/reviews/{id}{._format}')]),
        ]);
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
