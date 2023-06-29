<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Story\DefaultBookStory;
use App\DataFixtures\Story\DefaultDownloadStory;
use App\DataFixtures\Story\DefaultReviewsStory;
use App\DataFixtures\Story\DefaultUsersStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        DefaultUsersStory::load();
        DefaultBookStory::load();
        DefaultReviewsStory::load();
        DefaultDownloadStory::load();
    }
}
