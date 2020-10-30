<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * This entity represents a "most borrowed book" in a given a given French library.
 * It is loaded from a CSV file. It's purpose it to show how to expose data coming
 * from external data sources through API Platform.
 *
 * @see https://www.data.gouv.fr/fr/datasets/top-100-romans-adultes-de-science-fiction-et-fantastiques/
 * @see https://www.data.gouv.fr/fr/datasets/r/a2b09081-6cc4-4fdb-bd73-35484014c89c
 * @see /data/top-100-novel-sci-fi-fr.csv
 *
 * @ApiResource(
 *   collectionOperations={"get"},
 *   itemOperations={"get"}
 * )
 */
class TopBook
{
    /**
     * This is a fake ID so we can test the get "item-by-id" operation.
     *
     * @ApiProperty(identifier=true)
     */
    private ?int $id = null;

    /**
     * @var ?string
     */
    protected ?string $title;

    /**
     * @var ?string
     */
    protected ?string $author;

    /**
     * Tille and part of the book.
     *
     * @var ?string
     */
    protected ?string $part;

    /**
     * Place the book can be found in the library.
     *
     * @var ?string
     */
    protected ?string $place;

    /**
     * Number of times the book has been borrowed during one year.
     *
     * @var ?int
     */
    protected ?int $borrowCount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): TopBook
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): TopBook
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): TopBook
    {
        $this->author = $author;

        return $this;
    }

    public function getPart(): ?string
    {
        return $this->part;
    }

    public function setPart(?string $part): TopBook
    {
        $this->part = $part;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): TopBook
    {
        $this->place = $place;

        return $this;
    }

    public function getBorrowCount(): ?int
    {
        return $this->borrowCount;
    }

    public function setBorrowCount(?int $borrowCount): TopBook
    {
        $this->borrowCount = $borrowCount;

        return $this;
    }
}
