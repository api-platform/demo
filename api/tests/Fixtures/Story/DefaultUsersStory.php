<?php

namespace App\Tests\Fixtures\Story;

use App\Tests\Fixtures\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class DefaultUsersStory extends Story
{
    public function build(): void
    {
        // Create default admin user
        UserFactory::createOne([
            'email' => 'admin@example.com',
            'password' => '$2y$13$ObPHZr.cTpY6AteRdReHdO/2JwXj6fQesk8m3NL6gqEf6APlQ/po.', // admin
            'roles' => ['ROLE_ADMIN'],
        ]);
    }
}
