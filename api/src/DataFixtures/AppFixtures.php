<?php

namespace App\DataFixtures;

use App\DataFixtures\Story\DefaultBooksStory;
use App\DataFixtures\Story\DefaultReviewsStory;
use App\DataFixtures\Story\DefaultUsersStory;
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
