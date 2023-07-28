<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\BookFactory;
use App\DataFixtures\Factory\ReviewFactory;
use App\DataFixtures\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class DefaultReviewsStory extends Story
{
    public function build(): void
    {
        ReviewFactory::createOne([
            'book' => BookFactory::find(['book' => 'https://openlibrary.org/books/OL26210211M.json']),
            'user' => UserFactory::find(['email' => 'john.doe@example.com']),
            'rating' => 5,
        ]);

        ReviewFactory::createMany(99);
    }
}
