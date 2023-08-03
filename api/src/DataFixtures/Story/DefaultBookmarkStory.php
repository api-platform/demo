<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\BookFactory;
use App\DataFixtures\Factory\BookmarkFactory;
use App\DataFixtures\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class DefaultBookmarkStory extends Story
{
    public function build(): void
    {
        BookmarkFactory::createOne([
            'book' => BookFactory::find(['book' => 'https://openlibrary.org/books/OL6095440M.json']),
            'user' => UserFactory::find(['email' => 'john.doe@example.com']),
        ]);

        BookmarkFactory::createMany(99);
    }
}
