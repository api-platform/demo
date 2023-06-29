<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\DownloadFactory;
use Zenstruck\Foundry\Story;

final class DefaultDownloadStory extends Story
{
    public function build(): void
    {
        DownloadFactory::createMany(100);
    }
}
