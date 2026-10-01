<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Book;
use App\Mcp\Input\BookSearch;
use App\Repository\BookRepository;

/**
 * Query parameters (filters) are read from the HTTP query string, which MCP tool calls don't have:
 * the search is done from the tool arguments instead.
 *
 * @implements ProcessorInterface<BookSearch, Book[]>
 */
final readonly class BookSearchProcessor implements ProcessorInterface
{
    // Keeps the LLM context small
    public const int LIMIT = 30;

    public function __construct(
        private BookRepository $repository,
    ) {
    }

    /**
     * @param BookSearch $data
     *
     * @return Book[]
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return $this->repository->search($data->title, $data->author, self::LIMIT);
    }
}
