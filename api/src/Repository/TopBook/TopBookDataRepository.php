<?php

declare(strict_types=1);

namespace App\Repository\TopBook;

use RuntimeException;
use App\Entity\TopBook;

final class TopBookDataRepository implements TopBookDataInterface
{
    /**
     * @var string
     */
    private const DATA_SOURCE = 'top-100-novel-sci-fi-fr.csv';

    /**
     * @var int
     */
    private const FIELDS_COUNT = 5;

    /**
     * @return array<int, TopBook>
     */
    public function getTopBooks(): array
    {
        return $this->getFromCsv();
    }

    /**
     * Be careful that the file is a simple csv file without "enclosure". That means
     * a field can't contain a ";" or this would add an extra column to the row.
     * Consider using a more robust library like csv reader from the PHP league.
     *
     * @see https://csv.thephpleague.com
     *
     * @return array<int, TopBook>
     */
    public function getFromCsv(): array
    {
        $data = [];
        foreach ($this->getFileAsArray() as $line) {
            $data[] = str_getcsv($line, ';');
        }

        $cpt = 0;
        foreach ($data as $row) {
            if (1 === ++$cpt) {
                continue;
            }

            if (self::FIELDS_COUNT !== count($row)) {
                throw new RuntimeException(sprintf('Invalid data at row: %d', count($row)));
            }

            $topBook = new TopBook(
                $cpt - 1,
                $this->sanitize($row[0] ?? ''),
                $this->sanitize($row[1] ?? ''),
                $this->sanitize($row[2] ?? ''),
                $this->sanitize($row[3] ?? ''),
                (int) ($row[4] ?? 0),
            );
            $topBooks[$cpt - 1] = $topBook;
        }

        return $topBooks ?? [];
    }

    /**
     * @return array<int, string>
     */
    private function getFileAsArray(): array
    {
        $csvFileName = __DIR__.'/data/'.self::DATA_SOURCE;
        if (!is_file($csvFileName)) {
            throw new RuntimeException(sprintf("Can't find data source: %s", $csvFileName));
        }

        $file = file($csvFileName);
        if (!is_array($file)) {
            throw new RuntimeException(sprintf("Can't load data source: %s", $csvFileName));
        }

        return $file;
    }

    /**
     * The CSV file is a "ISO-8859-1" encoded file with French accents.
     */
    private function sanitize(?string $str): string
    {
        return trim(utf8_encode((string) $str));
    }
}
