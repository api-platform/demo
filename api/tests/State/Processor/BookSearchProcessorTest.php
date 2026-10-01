<?php

declare(strict_types=1);

namespace App\Tests\State\Processor;

use ApiPlatform\Metadata\McpToolCollection;
use App\Entity\Book;
use App\Mcp\Input\BookSearch;
use App\Repository\BookRepository;
use App\State\Processor\BookSearchProcessor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BookSearchProcessorTest extends TestCase
{
    #[Test]
    public function itSearchesBooksFromToolArguments(): void
    {
        $book = $this->createStub(Book::class);
        $data = new BookSearch();
        $data->title = 'Hyperion';
        $data->author = 'Simmons';

        $repositoryMock = $this->createMock(BookRepository::class);
        $repositoryMock
            ->expects($this->once())
            ->method('search')
            ->with('Hyperion', 'Simmons', BookSearchProcessor::LIMIT)
            ->willReturn([$book])
        ;

        $processor = new BookSearchProcessor($repositoryMock);

        $this->assertSame([$book], $processor->process($data, new McpToolCollection()));
    }
}
