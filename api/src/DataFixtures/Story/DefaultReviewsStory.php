<?php

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\ReviewFactory;
use Zenstruck\Foundry\Story;

final class DefaultReviewsStory extends Story
{
    public function build(): void
    {
        ReviewFactory::createMany(100);
    }
}
