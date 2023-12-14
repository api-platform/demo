<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Book;
use App\State\Processor\BookPersistProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class BookPersistProcessorTest extends TestCase
{
    private MockObject|ProcessorInterface $persistProcessorMock;
    private MockObject|ProcessorInterface $mercureProcessorMock;
    private HttpClientInterface|MockObject $clientMock;
    private MockObject|ResponseInterface $responseMock;
    private DecoderInterface|MockObject $decoderMock;
    private Book|MockObject $objectMock;
    private MockObject|Operation $operationMock;
    private BookPersistProcessor $processor;

    protected function setUp(): void
    {
        $this->persistProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->mercureProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->clientMock = $this->createMock(HttpClientInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->decoderMock = $this->createMock(DecoderInterface::class);
        $this->objectMock = $this->createMock(Book::class);
        $this->objectMock->book = 'https://openlibrary.org/books/OL2055137M.json';
        $this->operationMock = $this->createMock(Operation::class);

        $this->processor = new BookPersistProcessor(
            $this->persistProcessorMock,
            $this->mercureProcessorMock,
            $this->clientMock,
            $this->decoderMock
        );
    }

    /**
     * @test
     */
    public function itUpdatesBookDataBeforeSaveAndSendMercureUpdates(): void
    {
        $expectedData = $this->objectMock;
        $expectedData->title = 'Foundation';
        $expectedData->author = 'Dan Simmons';

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                [
                    Request::METHOD_GET, 'https://openlibrary.org/books/OL2055137M.json', [
                        'headers' => [
                            'Accept' => 'application/json',
                        ],
                    ],
                ],
                [
                    Request::METHOD_GET, 'https://openlibrary.org/authors/OL34221A.json', [
                        'headers' => [
                            'Accept' => 'application/json',
                        ],
                    ],
                ],
            )
            ->willReturnOnConsecutiveCalls($this->responseMock, $this->responseMock)
        ;
        $this->responseMock
            ->expects($this->exactly(2))
            ->method('getContent')
            ->willReturnOnConsecutiveCalls(
                json_encode([
                    'title' => 'Foundation',
                    'authors' => [
                        ['key' => '/authors/OL34221A'],
                    ],
                ]),
                json_encode([
                    'name' => 'Dan Simmons',
                ]),
            )
        ;
        $this->decoderMock
            ->expects($this->exactly(2))
            ->method('decode')
            ->withConsecutive(
                [
                    json_encode([
                        'title' => 'Foundation',
                        'authors' => [
                            ['key' => '/authors/OL34221A'],
                        ],
                    ]),
                    'json',
                ],
                [
                    json_encode([
                        'name' => 'Dan Simmons',
                    ]),
                    'json',
                ],
            )
            ->willReturnOnConsecutiveCalls(
                [
                    'title' => 'Foundation',
                    'authors' => [
                        ['key' => '/authors/OL34221A'],
                    ],
                ],
                [
                    'name' => 'Dan Simmons',
                ],
            )
        ;
        $this->persistProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($expectedData, $this->operationMock, [], [])
            ->willReturn($expectedData)
        ;
        $this->mercureProcessorMock
            ->expects($this->exactly(2))
            ->method('process')
            ->withConsecutive(
                [$expectedData, $this->operationMock, [], ['item_uri_template' => '/admin/books/{id}{._format}']],
                [$expectedData, $this->operationMock, [], ['item_uri_template' => '/books/{id}{._format}']],
            )
            ->willReturnOnConsecutiveCalls(
                $expectedData,
                $expectedData,
            )
        ;

        $this->assertEquals($expectedData, $this->processor->process($this->objectMock, $this->operationMock));
    }
}
