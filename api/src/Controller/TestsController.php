<?php declare(strict_types=1);

namespace App\Controller;

use App\DataProvider\TopBookDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manual test.
 */
class TestsController extends AbstractController
{
    private TopBookDataProvider $topBookDataProvider;

    public function __construct(TopBookDataProvider $topBookDataProvider)
    {
        $this->topBookDataProvider = $topBookDataProvider;
    }

    /**
     * Manual tests.
     *
     * @Route("/tests", name="tests")
     */
    public function tests(Request $request): Response
    {
        $topBooks = $this->topBookDataProvider->getTopBooks();
        dump($topBooks);
        die();
    }
}
