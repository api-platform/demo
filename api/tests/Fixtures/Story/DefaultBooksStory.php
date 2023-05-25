<?php

namespace App\Tests\Fixtures\Story;

use App\Tests\Fixtures\Factory\BookFactory;
use Zenstruck\Foundry\Story;

final class DefaultBooksStory extends Story
{
    public function build(): void
    {
        BookFactory::createMany(100);
        BookFactory::createOne(['archivedAt' => new \DateTimeImmutable('2021-09-10')]);
    }
}
