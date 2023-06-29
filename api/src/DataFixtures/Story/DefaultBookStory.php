<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\BookFactory;
use Zenstruck\Foundry\Story;

final class DefaultBookStory extends Story
{
    public function build(): void
    {
        BookFactory::createMany(100);
    }
}
