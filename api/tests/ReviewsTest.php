<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\Book;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

final class ReviewsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private Client $client;

    protected function setup(): void
    {
        $this->client = static::createClient();
    }

    public function testFilterReviewsByBook(): void
    {
        $iri = $this->findIriBy(Book::class, ['isbn' => '9786644879585']);
        $response = $this->client->request('GET', "/reviews?book=$iri");
        self::assertCount(2, $response->toArray()['hydra:member']);
    }

    public function testBookSubresource(): void
    {
        $iri = $this->findIriBy(Book::class, ['isbn' => '9786644879585']);
        $response = $this->client->request('GET', "$iri/reviews");
        self::assertCount(2, $response->toArray()['hydra:member']);
    }
}
