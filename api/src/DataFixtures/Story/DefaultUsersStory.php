<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class DefaultUsersStory extends Story
{
    public function build(): void
    {
        UserFactory::createMany(10);
        UserFactory::createOne([
            'email' => 'admin@example.com',
            'firstName' => 'Chuck',
            'lastName' => 'Norris',
            'roles' => ['ROLE_ADMIN'],
        ]);
    }
}
