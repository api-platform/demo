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
use App\Entity\Book;
use App\State\Processor\BookRemoveProcessor;
use App\State\Processor\MercureProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BookRemoveProcessorTest extends TestCase
{
    private MockObject|ProcessorInterface $removeProcessorMock;
    private MockObject|ProcessorInterface $mercureProcessorMock;
    private MockObject|ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactoryMock;
    private ResourceMetadataCollection $resourceMetadataCollection;
    private IriConverterInterface|MockObject $iriConverterMock;
    private Book|MockObject $objectMock;
    private MockObject|Operation $operationMock;
    private BookRemoveProcessor $processor;

    protected function setUp(): void
    {
        $this->removeProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->mercureProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->resourceMetadataCollectionFactoryMock = $this->createMock(ResourceMetadataCollectionFactoryInterface::class);
        $this->resourceMetadataCollection = new ResourceMetadataCollection(Book::class, [
            new ApiResource(operations: [new Get('/admin/books/{id}{._format}')]),
            new ApiResource(operations: [new Get('/books/{id}{._format}')]),
        ]);
        $this->iriConverterMock = $this->createMock(IriConverterInterface::class);
        $this->objectMock = $this->createMock(Book::class);
        $this->operationMock = $this->createMock(Operation::class);

        $this->processor = new BookRemoveProcessor(
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
                [Book::class],
                [Book::class],
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
                [$this->objectMock, UrlGeneratorInterface::ABS_URL, new Get('/admin/books/{id}{._format}')],
                [$this->objectMock, UrlGeneratorInterface::ABS_URL, new Get('/books/{id}{._format}')],
            )
            ->willReturnOnConsecutiveCalls(
                '/admin/books/9aff4b91-31cf-4e91-94b0-1d52bbe23fe6',
                '/books/9aff4b91-31cf-4e91-94b0-1d52bbe23fe6',
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
                        'item_uri_template' => '/admin/books/{id}{._format}',
                        MercureProcessor::DATA => json_encode(['@id' => '/admin/books/9aff4b91-31cf-4e91-94b0-1d52bbe23fe6']),
                    ],
                ],
                [
                    $this->objectMock,
                    $this->operationMock,
                    [],
                    [
                        'item_uri_template' => '/books/{id}{._format}',
                        MercureProcessor::DATA => json_encode(['@id' => '/books/9aff4b91-31cf-4e91-94b0-1d52bbe23fe6']),
                    ],
                ],
            )
        ;

        $this->processor->process($this->objectMock, $this->operationMock);
    }
}
