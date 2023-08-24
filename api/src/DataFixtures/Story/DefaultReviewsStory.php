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
        ReviewFactory::createMany(30, [
            'book' => BookFactory::find(['book' => 'https://openlibrary.org/books/OL6095440M.json']),
            'publishedAt' => \DateTimeImmutable::createFromMutable(ReviewFactory::faker()->dateTime('-1 week')),
        ]);
        ReviewFactory::createOne([
            'book' => BookFactory::find(['book' => 'https://openlibrary.org/books/OL6095440M.json']),
            'user' => UserFactory::find(['email' => 'john.doe@example.com']),
            'rating' => 5,
            'publishedAt' => new \DateTimeImmutable('-1 day'),
        ]);

        ReviewFactory::createMany(99);
    }
}
