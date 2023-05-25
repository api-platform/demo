<?php

namespace App\Tests\Fixtures\Story;

use App\Tests\Fixtures\Factory\ReviewFactory;
use Zenstruck\Foundry\Story;

final class DefaultReviewsStory extends Story
{
    public function build(): void
    {
        ReviewFactory::createMany(500);
    }
}
