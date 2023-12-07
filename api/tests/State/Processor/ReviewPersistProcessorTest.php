<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Review;
use App\Entity\User;
use App\State\Processor\ReviewPersistProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Clock\MockClock;

final class ReviewPersistProcessorTest extends TestCase
{
    private MockObject|ProcessorInterface $persistProcessorMock;
    private MockObject|ProcessorInterface $mercureProcessorMock;
    private MockObject|Security $securityMock;
    private MockObject|User $userMock;
    private MockObject|Review $objectMock;
    private ClockInterface|MockObject $clockMock;
    private ReviewPersistProcessor $processor;

    protected function setUp(): void
    {
        $this->persistProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->mercureProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->securityMock = $this->createMock(Security::class);
        $this->userMock = $this->createMock(User::class);
        $this->objectMock = $this->createMock(Review::class);
        $this->clockMock = new MockClock();

        $this->processor = new ReviewPersistProcessor(
            $this->persistProcessorMock,
            $this->mercureProcessorMock,
            $this->securityMock,
            $this->clockMock
        );
    }

    /**
     * @test
     */
    public function itUpdatesReviewDataFromOperationBeforeSaveAndSendMercureUpdates(): void
    {
        $operation = new Post();

        $expectedData = $this->objectMock;
        $expectedData->user = $this->userMock;
        $expectedData->publishedAt = $this->clockMock->now();

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
        $this->mercureProcessorMock
            ->expects($this->exactly(2))
            ->method('process')
            ->withConsecutive(
                [$expectedData, $operation, [], ['item_uri_template' => '/admin/reviews/{id}{._format}']],
                [$expectedData, $operation, [], ['item_uri_template' => '/books/{bookId}/reviews/{id}{._format}']],
            )
            ->willReturnOnConsecutiveCalls(
                $expectedData,
                $expectedData,
            )
        ;

        $this->assertEquals($expectedData, $this->processor->process($this->objectMock, $operation));
    }

    /**
     * @test
     */
    public function itUpdatesReviewDataFromContextBeforeSaveAndSendMercureUpdates(): void
    {
        $operation = $this->createMock(Operation::class);

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
        $this->mercureProcessorMock
            ->expects($this->exactly(2))
            ->method('process')
            ->withConsecutive(
                [$expectedData, $operation, [], $context + ['item_uri_template' => '/admin/reviews/{id}{._format}']],
                [$expectedData, $operation, [], $context + ['item_uri_template' => '/books/{bookId}/reviews/{id}{._format}']],
            )
            ->willReturnOnConsecutiveCalls(
                $expectedData,
                $expectedData,
            )
        ;

        $this->assertEquals($expectedData, $this->processor->process($this->objectMock, $operation, [], $context));
    }
}
