<?php

declare(strict_types=1);

namespace App\Mcp\Input;

use ApiPlatform\Metadata\ApiProperty;

/**
 * Arguments of the "add_review" MCP tool.
 */
final class ReviewInput
{
    #[ApiProperty(description: 'The reviewed book identifier (UUID).', schema: ['type' => 'string', 'format' => 'uuid'])]
    public string $bookId;

    #[ApiProperty(description: 'The review text.')]
    public string $body;

    #[ApiProperty(description: 'The rating, from 0 to 5.', schema: ['type' => 'integer', 'minimum' => 0, 'maximum' => 5])]
    public int $rating;
}
