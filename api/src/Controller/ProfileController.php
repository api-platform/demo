<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProfileController extends AbstractController
{
    #[Route(path: '/profile')]
    public function __invoke(Security $security): Response
    {
        return $this->json($security->getUser());
    }
}
