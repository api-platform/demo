<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bookmark;
use App\Entity\User;
use App\State\Processor\BookmarkPersistProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Clock\MockClock;

final class BookmarkPersistProcessorTest extends TestCase
{
    private MockObject|ProcessorInterface $persistProcessorMock;
    private MockObject|Security $securityMock;
    private MockObject|User $userMock;
    private Bookmark|MockObject $objectMock;
    private MockObject|Operation $operationMock;
    private ClockInterface|MockObject $clockMock;
    private BookmarkPersistProcessor $processor;

    protected function setUp(): void
    {
        $this->persistProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->securityMock = $this->createMock(Security::class);
        $this->userMock = $this->createMock(User::class);
        $this->objectMock = $this->createMock(Bookmark::class);
        $this->operationMock = $this->createMock(Operation::class);
        $this->clockMock = new MockClock();

        $this->processor = new BookmarkPersistProcessor($this->persistProcessorMock, $this->securityMock, $this->clockMock);
    }

    /**
     * @test
     */
    public function itUpdatesBookmarkDataBeforeSave(): void
    {
        $expectedData = $this->objectMock;
        $expectedData->user = $this->userMock;
        $expectedData->bookmarkedAt = $this->clockMock->now();

        $this->securityMock
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock)
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
