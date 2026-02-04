<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Review;
use App\Entity\User;
use App\Security\Http\Protection\ResourceHandlerInterface;
use App\State\Processor\ReviewPersistProcessor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Clock\MockClock;

final class ReviewPersistProcessorTest extends TestCase
{
    private MockObject $persistProcessorMock;

    private MockObject $securityMock;

    private Stub $userMock;

    private Stub $objectMock;

    private MockClock $clockMock;

    private MockObject $resourceHandlerMock;

    private ReviewPersistProcessor $processor;

    protected function setUp(): void
    {
        $this->persistProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->securityMock = $this->createMock(Security::class);
        $this->userMock = $this->createStub(User::class);
        $this->objectMock = $this->createStub(Review::class);
        $this->clockMock = new MockClock();
        $this->resourceHandlerMock = $this->createMock(ResourceHandlerInterface::class);

        $this->processor = new ReviewPersistProcessor(
            $this->persistProcessorMock,
            $this->securityMock,
            $this->clockMock,
            $this->resourceHandlerMock
        );
    }

    #[Test]
    public function itUpdatesReviewDataFromOperationBeforeSaveAndSendMercureUpdates(): void
    {
        $operation = new Post();

        $expectedData = $this->objectMock;
        $expectedData->user = $this->userMock;
        $expectedData->publishedAt = $this->clockMock->now();

        $this->userMock->email = 'john.doe@example.com';
        $this->securityMock
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock)
        ;
        $this->persistProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($expectedData, $operation, [], [])
            ->willReturn($expectedData)
        ;
        $this->resourceHandlerMock
            ->expects($this->once())
            ->method('create')
            ->with($expectedData, $this->userMock, [
                'operation_name' => '/books/{bookId}/reviews/{id}{._format}',
            ])
        ;

        $this->assertEquals($expectedData, $this->processor->process($this->objectMock, $operation));
    }

    #[Test]
    public function itUpdatesReviewDataFromContextBeforeSaveAndSendMercureUpdates(): void
    {
        $operation = $this->createStub(Operation::class);

        $previousData = new Review();
        $previousData->publishedAt = $this->clockMock->now();
        $previousData->user = $this->userMock;

        $context = ['previous_data' => $previousData];

        $expectedData = $this->objectMock;
        $expectedData->user = $previousData->user;
        $expectedData->publishedAt = $previousData->publishedAt;

        $this->securityMock
            ->expects($this->never())
            ->method('getUser')
        ;
        $this->persistProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($expectedData, $operation, [], $context)
            ->willReturn($expectedData)
        ;
        $this->resourceHandlerMock
            ->expects($this->never())
            ->method('create')
        ;

        $this->assertEquals($expectedData, $this->processor->process($this->objectMock, $operation, [], $context));
    }
}
