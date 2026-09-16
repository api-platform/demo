<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bookmark;
use App\Entity\User;
use App\State\Processor\BookmarkPersistProcessor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Clock\MockClock;

final class BookmarkPersistProcessorTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\ApiPlatform\State\ProcessorInterface
     */
    private MockObject $persistProcessorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Bundle\SecurityBundle\Security
     */
    private MockObject $securityMock;

    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\App\Entity\User
     */
    private Stub $userMock;

    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\App\Entity\Bookmark
     */
    private Stub $objectMock;

    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\ApiPlatform\Metadata\Operation
     */
    private Stub $operationMock;

    private MockClock $clockMock;

    private BookmarkPersistProcessor $processor;

    protected function setUp(): void
    {
        $this->persistProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->securityMock = $this->createMock(Security::class);
        $this->userMock = $this->createStub(User::class);
        $this->objectMock = $this->createStub(Bookmark::class);
        $this->operationMock = $this->createStub(Operation::class);
        $this->clockMock = new MockClock();

        $this->processor = new BookmarkPersistProcessor($this->persistProcessorMock, $this->securityMock, $this->clockMock);
    }

    #[Test]
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
