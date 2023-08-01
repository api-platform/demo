<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Story\DefaultBookmarkStory;
use App\DataFixtures\Story\DefaultBookStory;
use App\DataFixtures\Story\DefaultReviewsStory;
use App\DataFixtures\Story\DefaultUsersStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        DefaultBookStory::load();
        DefaultUsersStory::load();
        DefaultReviewsStory::load();
        DefaultBookmarkStory::load();
    }
}
