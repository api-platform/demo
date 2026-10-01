<?php

declare(strict_types=1);

namespace App\Mcp\Input;

use ApiPlatform\Metadata\ApiProperty;

/**
 * Arguments of the "search_books" MCP tool.
 */
final class BookSearch
{
    // Explicit schemas avoid ["string", "null"] unions, which some LLMs reject
    #[ApiProperty(description: 'Part of the book title, case-insensitive.', schema: ['type' => 'string'])]
    public ?string $title = null;

    #[ApiProperty(description: 'Part of the author name, case-insensitive.', schema: ['type' => 'string'])]
    public ?string $author = null;
}
