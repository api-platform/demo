<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\BookFactory;
use App\Enum\BookCondition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Zenstruck\Foundry\Story;

final class DefaultBookStory extends Story
{
    public function __construct(
        private readonly DecoderInterface $decoder,
        private readonly HttpClientInterface $client,
    ) {
    }

    public function build(): void
    {
        BookFactory::createOne([
            'condition' => BookCondition::UsedCondition,
            'book' => 'https://openlibrary.org/books/OL6095440M.json',
            'title' => 'Foundation',
            'author' => 'Isaac Asimov',
        ]);

        $offset = 0;
        $limit = 99;

        while ($offset < $limit) {
            /* @see https://openlibrary.org/dev/docs/restful_api */
            $uri = 'https://openlibrary.org/query?type=/type/edition&languages=/languages/eng&subjects=Science%20Fiction&authors=&covers=&title=&description=&publish_date&offset='.$offset;
            $books = $this->getData($uri);
            foreach ($books as $book) {
                $datum = [
                    'condition' => BookCondition::cases()[array_rand(BookCondition::cases())],
                    'book' => 'https://openlibrary.org'.$book['key'].'.json',
                    'title' => $book['title'],
                ];

                if (isset($book['authors'][0]['key'])) {
                    $author = $this->getData('https://openlibrary.org'.$book['authors'][0]['key']);
                    if (isset($author['name'])) {
                        $datum['author'] = $author['name'];
                    }
                }

                BookFactory::createOne($datum);
                if (++$offset === $limit) {
                    break 2;
                }
            }
        }
    }

    private function getData(string $uri): array
    {
        return $this->decoder->decode($this->client->request(Request::METHOD_GET, $uri, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ])->getContent(), 'json');
    }
}
