<?php

declare(strict_types=1);

namespace App\BookRepository\Exception;

final class UnsupportedBookException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Book is not supported yet.');
    }
}
