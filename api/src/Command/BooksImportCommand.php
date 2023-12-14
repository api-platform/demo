<?php

declare(strict_types=1);

namespace App\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:books:import',
    description: 'Import books from Open Library and store their data in a JSON for fixtures loading.',
)]
final class BooksImportCommand extends Command
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly DecoderInterface $decoder,
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                name: 'limit',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Number of books to import',
                default: 99
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $offset = 0;
        $limit = (int) $input->getOption('limit');
        $io->progressStart($limit);
        $data = [];
        while ($offset < $limit) {
            /** @see https://openlibrary.org/dev/docs/restful_api */
            $uri = 'https://openlibrary.org/query?type=/type/edition&languages=/languages/eng&subjects=Science%20Fiction&authors=&covers=&title=&description=&publish_date&offset=' . $offset;
            $books = $this->getData($uri);
            foreach ($books as $book) {
                $this->logger->info('Importing book.', [
                    'book' => 'https://openlibrary.org' . $book['key'] . '.json',
                ]);

                $datum = [
                    'book' => 'https://openlibrary.org' . $book['key'] . '.json',
                    'title' => $book['title'],
                ];

                if (isset($book['authors'][0]['key'])) {
                    $author = $this->getData('https://openlibrary.org' . $book['authors'][0]['key']);
                    if (isset($author['name'])) {
                        $datum['author'] = $author['name'];
                    }
                }

                $data[] = $datum;

                $io->progressAdvance();
                if (++$offset === $limit) {
                    break 2;
                }
            }
        }

        $io->progressFinish();

        $output->write($this->serializer->serialize($data, 'json'));

        return Command::SUCCESS;
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
