<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A review of an item - for example, of a restaurant, movie, or store.
 *
 * @see http://schema.org/Review Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Review", attributes={"filters"={"review.search_filter"}})
 */
class Review
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string The actual body of the review
     *
     * @Assert\Type(type="string")
     * @ORM\Column(nullable=true, type="text")
     * @ApiProperty(iri="http://schema.org/reviewBody")
     */
    private $body;

    /**
     * @var int
     *
     * @Assert\Type(type="integer")
     * @Assert\Range(min=0, max=5)
     * @ORM\Column(type="smallint")
     */
    private $rating;

    /**
     * @var Book The item that is being reviewed/rated
     *
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="App\Entity\Book")
     * @ORM\JoinColumn(nullable=false)
     * @ApiProperty(iri="http://schema.org/itemReviewed")
     */
    private $book;

    /**
     * @var string Author the author of the review
     *
     * @ORM\Column(nullable=true, type="text")
     * @ApiProperty(iri="http://schema.org/author")
     */
    private $author;

    /**
     * @var \DateTime Author the author of the review
     *
     * @ORM\Column(nullable=true, type="datetime")
     */
    private $publicationDate;

    /**
     * Sets id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets rating.
     *
     * @param int $rating
     *
     * @return $this
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Gets rating.
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Get body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set body.
     *
     * @param string $body the value to set
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get book.
     *
     * @return book
     */
    public function getBook()
    {
        return $this->book;
    }

    /**
     * Set book.
     *
     * @param Book $book the value to set
     */
    public function setBook(Book $book)
    {
        $this->book = $book;
    }

    /**
     * Get author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set author.
     *
     * @param string $author the value to set
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Get publicationDate.
     *
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Set publicationDate.
     *
     * @param \DateTime $publicationDate the value to set
     */
    public function setPublicationDate(\DateTime $publicationDate)
    {
        $this->publicationDate = $publicationDate;
    }
}
