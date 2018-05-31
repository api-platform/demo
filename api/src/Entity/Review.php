<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A review of an item - for example, of a restaurant, movie, or store.
 *
 * @see http://schema.org/Review Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Review")
 * @ApiFilter(SearchFilter::class, properties={"book": "exact"})
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
     * @ORM\Column(type="text", nullable=true)
     * @ApiProperty(iri="http://schema.org/reviewBody")
     */
    public $body;

    /**
     * @var int
     *
     * @Assert\Range(min=0, max=5)
     * @ORM\Column(type="smallint")
     */
    public $rating;

    /**
     * @var string DEPRECATED (use rating now): A letter to rate the book
     *
     * @Assert\Choice({"a", "b", "c", "d"})
     * @ORM\Column(type="string", nullable=true)
     * @ApiProperty(deprecationReason="Use the rating property instead")
     */
    public $letter;

    /**
     * @var Book The item that is being reviewed/rated
     *
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity=Book::class, inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     * @ApiProperty(iri="http://schema.org/itemReviewed")
     */
    public $book;

    /**
     * @var string Author the author of the review
     *
     * @ORM\Column(type="text", nullable=true)
     * @ApiProperty(iri="http://schema.org/author")
     */
    public $author;

    /**
     * @var \DateTimeInterface Author the author of the review
     *
     * @ORM\Column(nullable=true, type="datetime")
     */
    public $publicationDate;

    public function getId(): ?int
    {
        return $this->id;
    }
}
