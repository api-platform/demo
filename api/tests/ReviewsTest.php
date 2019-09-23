<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Book;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

final class ReviewsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testFilterReviewsByBook(): void
    {
        $client = static::createClient();
        $iri = static::findIriBy(Book::class, ['isbn' => '9786644879585']);

        $response = $client->request('GET', "/reviews?book=$iri");
        $this->assertCount(2, $response->toArray()['hydra:member']);
    }

    public function testBookSubresource(): void
    {
        $client = static::createClient();
        $iri = static::findIriBy(Book::class, ['isbn' => '9786644879585']);

        $response = $client->request('GET', "$iri/reviews");
        $this->assertCount(2, $response->toArray()['hydra:member']);
    }
}
