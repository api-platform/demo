<?php

declare(strict_types=1);

namespace App\Tests\Api\Admin\Trait;

use App\DataFixtures\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;

trait UsersDataProviderTrait
{
    public function getNonAdminUsers(): iterable
    {
        yield [
            Response::HTTP_UNAUTHORIZED,
            'Full authentication is required to access this resource.',
            null,
        ];
        yield [
            Response::HTTP_FORBIDDEN,
            'Access Denied.',
            UserFactory::new(),
        ];
    }
}
