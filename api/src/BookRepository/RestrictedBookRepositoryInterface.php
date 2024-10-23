<?php

declare(strict_types=1);

namespace App\BookRepository;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: RestrictedBookRepositoryInterface::TAG)]
interface RestrictedBookRepositoryInterface extends BookRepositoryInterface
{
    public const TAG = 'book.repository';

    public function supports(string $url): bool;
}
