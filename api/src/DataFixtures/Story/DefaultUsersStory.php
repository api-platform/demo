<?php

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class DefaultUsersStory extends Story
{
    public function build(): void
    {
        UserFactory::createOne([
            'email' => 'admin@example.com',
            'roles' => ['ROLE_ADMIN'],
        ]);
    }
}
