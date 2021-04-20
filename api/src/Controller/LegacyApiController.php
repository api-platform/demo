<?php

declare(strict_types=1);

namespace App\Controller;

use App\OpenApi\OpenApiFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This is a dummy controller providing a fake API endpoint. This route is available
 * in the Swagger interface thanks to the OpenApiFactory service.
 *
 * @see OpenApiFactory
 */
final class LegacyApiController extends AbstractController
{
    #[Route(path: '/stats')]
    public function __invoke(): Response
    {
        return $this->json([
            'books_count' => 1000,
            'topbooks_count' => 100,
        ]);
    }
}
