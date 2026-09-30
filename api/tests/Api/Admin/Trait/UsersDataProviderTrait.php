<?php

declare(strict_types=1);

namespace App\Tests\Api\Admin\Trait;

use App\DataFixtures\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;

trait UsersDataProviderTrait
{
    public static function getNonAdminUsers(): iterable
    {
        yield [
            Response::HTTP_UNAUTHORIZED,
            'Access Denied.',
            null,
        ];
        yield [
            Response::HTTP_FORBIDDEN,
            'Access Denied.',
            UserFactory::new(),
        ];
    }
}
