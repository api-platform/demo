<?php declare(strict_types=1);

namespace App\DataProvider;

use App\Entity\TopBook;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class TopBookDataProvider
{
    private const MAX_CACHE_TIME = 1; // 1 hour
    private const DATA_SOURCE = 'top-100-novel-sci-fi-fr.csv';
    private const FIELDS_COUNT = 5;

    private CacheInterface $cache;
    private KernelInterface $kernel;

    public function __construct(CacheInterface $cache, KernelInterface $kernel)
    {
        $this->cache = $cache;
        $this->kernel = $kernel;
    }

    /**
     * Local caching is done so the CSV isn't reloaded at every call.
     */
    public function getTopBooks(): array
    {
        return $this->cache->get('books.sci-fi.top.fr', function (ItemInterface $item) {
            $item->expiresAfter(self::MAX_CACHE_TIME);

            return $this->getTopBooksFromCsv();
        });
    }

    /**
     * Be careful that the file is a simple csv file without "enclosure". That means
     * a field can't contain a ";" or this would add an extra column to the row.
     * Consider using a more robust library like csv reader from the PHP pleague.
     *
     * @see https://csv.thephpleague.com
     */
    public function getTopBooksFromCsv(): array
    {
        $csvFileName = $this->kernel->getProjectDir().'/data/'.self::DATA_SOURCE;
        if (!is_file($csvFileName)) {
            throw new \RuntimeException(sprintf("Can't find data source: %s", $csvFileName));
        }
        foreach (file($csvFileName) as $line) {
            $data[] = str_getcsv($line, ';');
        }

        $cpt = 0;
        foreach ($data ?? [] as $row) {
            if (++$cpt === 1) {
                continue;
            }
            if (count($row) !== self::FIELDS_COUNT) {
                throw new \RuntimeException(sprintf('Invalid data at row: %d', count($row)));
            }
            $topBooks[] = (new TopBook())
                ->setId($cpt-1)
                ->setTitle($this->sanitize($row[0] ?? null))
                ->setAuthor($this->sanitize($row[1] ?? null))
                ->setPart($this->sanitize($row[2] ?? null))
                ->setPlace($this->sanitize($row[3] ?? null))
                ->setBorrowCount((int) $row[4]);
        }

        return $topBooks ?? [];
    }

    /**
     * The csv file is a ISO-8859-1 encoded file with French accents.
     */
    private function sanitize(?string $str): string
    {
        return trim(utf8_encode($str));
    }
}
