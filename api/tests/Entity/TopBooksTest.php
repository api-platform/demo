<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\TopBook;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TopBooksTest extends KernelTestCase
{
    /**
     * @see TopBook::__construct()
     */
    public function testConstructNomicalCase(): void
    {
        $topBook = new TopBook(1, 'Title', 'Author','Title & part', 'Place', 10);
        self::assertInstanceOf(TopBook::class, $topBook);
    }

    /**
     * @see TopBook::__construct()
     */
    public function testConstructTypeError(): void
    {
        $this->expectException(\TypeError::class);
        new TopBook(1, 1, 1,1, 1, 10);
    }
}
