<?php

declare(strict_types=1);

namespace App\Mcp\Input;

use ApiPlatform\Metadata\ApiProperty;

/**
 * Arguments of the "get_book" MCP tool.
 */
final class BookIdentifier
{
    #[ApiProperty(description: 'The book identifier (UUID).', schema: ['type' => 'string', 'format' => 'uuid'])]
    public string $id;
}
