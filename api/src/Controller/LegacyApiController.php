<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegacyApiController extends AbstractController
{
    /**
     * @Route("/stats", name="api_book_stats")
     */
    public function stats(): Response
    {
        return $this->json([
            'books_count' => 1000,
            'topbooks_count' => 100,
        ]);
    }
}
