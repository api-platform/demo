<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\UserFactory;
use Zenstruck\Foundry\Story;

final class DefaultUsersStory extends Story
{
    public function build(): void
    {
        UserFactory::createOne([
            'email' => 'chuck.norris@example.com',
            'firstName' => 'Chuck',
            'lastName' => 'Norris',
            'roles' => ['ROLE_ADMIN'],
        ]);
        UserFactory::createOne([
            'email' => 'john.doe@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'roles' => ['ROLE_USER'],
        ]);
        UserFactory::createMany(10);
    }
}
