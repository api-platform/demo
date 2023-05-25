<?php

namespace App\DataFixtures;

use App\Tests\Fixtures\Story\DefaultBooksStory;
use App\Tests\Fixtures\Story\DefaultReviewsStory;
use App\Tests\Fixtures\Story\DefaultUsersStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        DefaultBooksStory::load();
        DefaultReviewsStory::load();
        DefaultUsersStory::load();
    }
}
