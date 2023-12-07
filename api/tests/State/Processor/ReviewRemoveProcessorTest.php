<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Review;
use App\State\Processor\MercureProcessor;
use App\State\Processor\ReviewRemoveProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ReviewRemoveProcessorTest extends TestCase
{
    private MockObject|ProcessorInterface $removeProcessorMock;
    private MockObject|ProcessorInterface $mercureProcessorMock;
    private MockObject|ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactoryMock;
    private ResourceMetadataCollection $resourceMetadataCollection;
    private IriConverterInterface|MockObject $iriConverterMock;
    private MockObject|Review $objectMock;
    private MockObject|Operation $operationMock;
    private ReviewRemoveProcessor $processor;

    protected function setUp(): void
    {
        $this->removeProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->mercureProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->resourceMetadataCollectionFactoryMock = $this->createMock(ResourceMetadataCollectionFactoryInterface::class);
        $this->resourceMetadataCollection = new ResourceMetadataCollection(Review::class, [
            new ApiResource(operations: [new Get('/admin/reviews/{id}{._format}')]),
            new ApiResource(operations: [new Get('/books/{bookId}/reviews/{id}{._format}')]),
        ]);
        $this->iriConverterMock = $this->createMock(IriConverterInterface::class);
        $this->objectMock = $this->createMock(Review::class);
        $this->operationMock = $this->createMock(Operation::class);

        $this->processor = new ReviewRemoveProcessor(
            $this->removeProcessorMock,
            $this->mercureProcessorMock,
            $this->resourceMetadataCollectionFactoryMock,
            $this->iriConverterMock
        );
    }

    /**
     * @test
     */
    public function itRemovesBookAndSendMercureUpdates(): void
    {
        $this->removeProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->objectMock, $this->operationMock, [], [])
        ;
        $this->resourceMetadataCollectionFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [Review::class],
                [Review::class],
            )
            ->willReturnOnConsecutiveCalls(
                $this->resourceMetadataCollection,
                $this->resourceMetadataCollection,
            )
        ;
        $this->iriConverterMock
            ->expects($this->exactly(2))
            ->method('getIriFromResource')
            ->withConsecutive(
                [$this->objectMock, UrlGeneratorInterface::ABS_URL, new Get('/admin/reviews/{id}{._format}')],
                [$this->objectMock, UrlGeneratorInterface::ABS_URL, new Get('/books/{bookId}/reviews/{id}{._format}')],
            )
            ->willReturnOnConsecutiveCalls(
                '/admin/reviews/9aff4b91-31cf-4e91-94b0-1d52bbe23fe6',
                '/books/8ad70d36-abaf-4c9b-aeaa-7ec63e6ca6f3/reviews/9aff4b91-31cf-4e91-94b0-1d52bbe23fe6',
            )
        ;
        $this->mercureProcessorMock
            ->expects($this->exactly(2))
            ->method('process')
            ->withConsecutive(
                [
                    $this->objectMock,
                    $this->operationMock,
                    [],
                    [
                        'item_uri_template' => '/admin/reviews/{id}{._format}',
                        MercureProcessor::DATA => json_encode(['@id' => '/admin/reviews/9aff4b91-31cf-4e91-94b0-1d52bbe23fe6']),
                    ],
                ],
                [
                    $this->objectMock,
                    $this->operationMock,
                    [],
                    [
                        'item_uri_template' => '/books/{bookId}/reviews/{id}{._format}',
                        MercureProcessor::DATA => json_encode(['@id' => '/books/8ad70d36-abaf-4c9b-aeaa-7ec63e6ca6f3/reviews/9aff4b91-31cf-4e91-94b0-1d52bbe23fe6']),
                    ],
                ],
            )
        ;

        $this->processor->process($this->objectMock, $this->operationMock);
    }
}
