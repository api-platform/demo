<?php

use App\Entity\Book;
use App\Entity\Review;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\MinkContext;
use Behatch\Context\JsonContext;
use Behatch\Context\RestContext;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Defines features for Book entity.
 */
class BookContext implements Context
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var RestContext
     */
    private $restContext;

    /**
     * @var MinkContext
     */
    private $minkContext;

    /**
     * @var JsonContext
     */
    private $jsonContext;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->restContext = $scope->getEnvironment()->getContext(RestContext::class);
        $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
        $this->jsonContext = $scope->getEnvironment()->getContext(JsonContext::class);
    }

    /**
     * @When I create a book
     */
    public function sendPostRequestToBooks(string $data = null)
    {
        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/ld+json');
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        if (null === $data) {
            $data = <<<'JSON'
{
  "isbn": "9781782164104",
  "title": "Persistence in PHP with the Doctrine ORM",
  "description": "This book is designed for PHP developers and architects who want to modernize their skills through better understanding of Persistence and ORM.",
  "author": "Kévin Dunglas",
  "publicationDate": "2013-12-01"
}
JSON;
        }
        $this->restContext->iSendARequestToWithBody('POST', '/books', new PyStringNode([$data], 0));
    }

    /**
     * @When I create a book with invalid data
     */
    public function sendPostRequestToBooksWithInvalidData()
    {
        $this->sendPostRequestToBooks(<<<'JSON'
{
  "isbn": "1312",
  "title": "",
  "description": "This book is designed for PHP developers and architects who want to modernize their skills through better understanding of Persistence and ORM.",
  "author": "Kévin Dunglas",
  "publicationDate": "2013-12-01"
}
JSON
        );
    }

    /**
     * @Then I see a book
     */
    public function checkPostBooksResponse()
    {
        $this->minkContext->assertResponseStatus(201);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/ld+json; charset=utf-8');
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([<<<'JSON'
{
  "type": "object",
  "properties": {
    "@context": {"pattern": "^/contexts/Book$"},
    "@id": {"pattern": "^/books/[\\w-;=]+$"},
    "@type": {"pattern": "^http://schema.org/Book$"},
    "isbn": {"pattern": "^9781782164104$"},
    "title": {"pattern": "^Persistence in PHP with the Doctrine ORM$"},
    "description": {"pattern": "^This book is designed for PHP developers and architects who want to modernize their skills through better understanding of Persistence and ORM.$"},
    "author": {"pattern": "^Kévin Dunglas$"},
    "publicationDate": {"pattern": "^2013\\-12\\-01T00:00:00\\+00:00$"},
    "reviews": {
      "type": "array",
      "minItems": 0,
      "maxItems": 0
    }
  }
}
JSON
        ], 0));
    }

    /**
     * @Then the request is invalid
     */
    public function requestIsInvalid()
    {
        $this->minkContext->assertResponseStatus(400);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/ld+json; charset=utf-8');
        $this->jsonContext->theJsonShouldBeEqualTo(new PyStringNode([<<<'JSON'
{
  "@context": "/contexts/ConstraintViolationList",
  "@type": "ConstraintViolationList",
  "hydra:title": "An error occurred",
  "hydra:description": "isbn: This value is neither a valid ISBN-10 nor a valid ISBN-13.\ntitle: This value should not be blank.",
  "violations": [
    {
      "propertyPath": "isbn",
      "message": "This value is neither a valid ISBN-10 nor a valid ISBN-13."
    },
    {
      "propertyPath": "title",
      "message": "This value should not be blank."
    }
  ]
}
JSON
        ], 0));
    }

    /**
     * @Given there is a book
     */
    public function createBook(bool $persist = true)
    {
        $book = new Book();
        $book->isbn = '9781782164104';
        $book->title = 'Persistence in PHP with the Doctrine ORM';
        $book->description = 'This book is designed for PHP developers and architects who want to modernize their skills through better understanding of Persistence and ORM.';
        $book->author = 'Kévin Dunglas';
        $book->publicationDate = new \DateTime('2013-12-01');

        if (true === $persist) {
            $em = $this->doctrine->getManager();
            $em->persist($book);
            $em->flush();
        }

        return $book;
    }

    /**
     * @When I get a list of books
     */
    public function sendGetRequestToBooks()
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        $this->restContext->iSendARequestTo('GET', '/books');
    }

    /**
     * @Then I see a list of books
     */
    public function checkGetBooksResponse()
    {
        $this->minkContext->assertResponseStatus(200);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/ld+json; charset=utf-8');
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([<<<'JSON'
{
  "type": "object",
  "properties": {
    "@context": {"pattern": "^/contexts/Book$"},
    "@id": {"pattern": "^/books$"},
    "@type": {"pattern": "^hydra:Collection"},
    "hydra:member": {
      "type": "array",
      "minItems": 1,
      "maxItems": 1,
      "items": [
        {
          "type": "object",
          "properties": {
            "@id": {"pattern": "^/books/[\\w-;=]+$"},
            "@type": {"pattern": "^http://schema.org/Book$"},
            "isbn": {"pattern": "^9781782164104$"},
            "title": {"pattern": "^Persistence in PHP with the Doctrine ORM$"},
            "description": {"pattern": "^This book is designed for PHP developers and architects who want to modernize their skills through better understanding of Persistence and ORM.$"},
            "author": {"pattern": "^Kévin Dunglas$"},
            "publicationDate": {"pattern": "^2013\\-12\\-01T00:00:00\\+00:00$"},
            "reviews": {
              "type": "array",
              "minItems": 0,
              "maxItems": 0
            }
          }
        }
      ]
    },
    "hydra:totalItems": {"type": "integer"},
    "hydra:search": {
      "type": "object",
      "properties": {
        "@type": {"pattern": "^hydra:IriTemplate$"},
        "hydra:template": {"pattern": "^/books\\{\\?properties\\[\\]\\,order\\[id\\],order\\[title\\],order\\[author\\],order\\[isbn\\],order\\[publicationDate\\],title,author}$"},
        "hydra:variableRepresentation": {"pattern": "^BasicRepresentation$"},
        "hydra:mapping": {
          "type": "array",
          "minItems": 8,
          "maxItems": 8,
          "items": [
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^properties\\[\\]$"},
              "property": {"type": "null"},
              "required": false
            },
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^order\\[id\\]$"},
              "property": {"pattern": "^id$"},
              "required": false
            },
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^order\\[title\\]$"},
              "property": {"pattern": "^title$"},
              "required": false
            },
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^order\\[author\\]$"},
              "property": {"pattern": "^author$"},
              "required": false
            },
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^order\\[isbn\\]$"},
              "property": {"pattern": "^isbn$"},
              "required": false
            },
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^order\\[publicationDate\\]$"},
              "property": {"pattern": "^publicationDate$"},
              "required": false
            },
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^title$"},
              "property": {"pattern": "^title$"},
              "required": false
            },
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^author$"},
              "property": {"pattern": "^author$"},
              "required": false
            }
          ]
        }
      }
    }
  }
}
JSON
        ], 0));
    }

    /**
     * @When I create a review
     */
    public function sendPostRequestToReviews()
    {
        /** @var Book $book */
        $book = $this->doctrine->getRepository(Book::class)->findOneBy([]);

        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/ld+json');
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        $this->restContext->iSendARequestToWithBody('POST', '/reviews', new PyStringNode([\sprintf(<<<'JSON'
{
  "rating": 5,
  "body": "Must have!",
  "author": "Foo Bar",
  "publicationDate": "2016-01-01",
  "book": "/books/%s"
}
JSON
            , $book->getId())], 0));
    }

    /**
     * @Then I see a review
     */
    public function checkPostReviewssResponse()
    {
        $this->minkContext->assertResponseStatus(201);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/ld+json; charset=utf-8');
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([<<<'JSON'
{
  "type": "object",
  "properties": {
    "@context": {"pattern": "^/contexts/Review$"},
    "@id": {"pattern": "^/reviews/[\\w-;=]+$"},
    "@type": {"pattern": "^http://schema.org/Review$"},
    "body": {"pattern": "^Must have!$"},
    "rating": {"type": "integer"},
    "letter": {"type": "null"},
    "author": {"pattern": "^Foo Bar$"},
    "publicationDate": {"pattern": "^2016\\-01\\-01T00:00:00\\+00:00$"},
    "book": {
      "type": "object",
      "properties": {
        "@id": {"pattern": "^/books/[\\w-;=]+$"},
        "@type": {"pattern": "^http://schema.org/Book$"},
        "title": {"pattern": "^Persistence in PHP with the Doctrine ORM$"}
      }
    }
  }
}
JSON
        ], 0));
    }

    /**
     * @Given there is a book with a review
     */
    public function createReview()
    {
        $book = $this->createBook(false);

        $review = new Review();
        $review->setBook($book);
        $review->rating = 5;
        $review->body = 'Must have!';
        $review->author = 'Foo Bar';
        $review->publicationDate = new \DateTime('2016-01-01');

        $em = $this->doctrine->getManager();
        $em->persist($review);
        $em->persist($book);
        $em->flush();
    }

    /**
     * @When I get a list of reviews filtered by book
     */
    public function sendGetRequestToReviewsFilteredByBook()
    {
        /** @var Book $book */
        $book = $this->doctrine->getRepository(Book::class)->findOneBy([]);

        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        $this->restContext->iSendARequestTo('GET', '/reviews?book=/books/'.$book->getId());
    }

    /**
     * @Then I see a list of reviews
     */
    public function checkGetReviewsResponse()
    {
        $this->minkContext->assertResponseStatus(200);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/ld+json; charset=utf-8');
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([<<<'JSON'
{
  "type": "object",
  "properties": {
    "@context": {"pattern": "^/contexts/Review$"},
    "@id": {"pattern": "^/reviews$"},
    "@type": {"pattern": "^hydra:Collection"},
    "hydra:member": {
      "type": "array",
      "minItems": 1,
      "maxItems": 1,
      "items": [
        {
          "type": "object",
          "properties": {
            "@id": {"pattern": "^/reviews/[\\w-;=]+$"},
            "@type": {"pattern": "^http://schema.org/Review$"},
            "body": {"pattern": "^Must have!$"},
            "rating": {"type": "integer"},
            "letter": {"type": "null"},
            "author": {"pattern": "^Foo Bar$"},
            "publicationDate": {"pattern": "^2016\\-01\\-01T00:00:00\\+00:00$"},
            "book": {
              "type": "object",
              "properties": {
                "@id": {"pattern": "^/books/[\\w-;=]+$"},
                "@type": {"pattern": "^http://schema.org/Book$"},
                "title": {"pattern": "^Persistence in PHP with the Doctrine ORM$"}
              }
            }
          }
        }
      ]
    },
    "hydra:totalItems": {"type": "integer"},
    "hydra:view": {
      "type": "object",
      "properties": {
        "@id": {"pattern": "^/reviews\\?book=%2Fbooks%2F[\\w-;=]+$"},
        "@type": {"pattern": "^hydra:PartialCollectionView$"}
      }
    },
    "hydra:search": {
      "type": "object",
      "properties": {
        "@type": {"pattern": "^hydra:IriTemplate$"},
        "hydra:template": {"pattern": "^/reviews{\\?order\\[id\\],order\\[publicationDate\\],book,book\\[\\]}$"},
        "hydra:variableRepresentation": {"pattern": "^BasicRepresentation$"},
        "hydra:mapping": {
          "type": "array",
          "minItems": 4,
          "maxItems": 4,
          "items": [
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^order\\[id\\]$"},
              "property": {"pattern": "^id$"},
              "required": false
            },
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^order\\[publicationDate\\]$"},
              "property": {"pattern": "^publicationDate$"},
                "required": false
            },
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^book$"},
              "property": {"pattern": "^book$"},
              "required": false
            },
            {
              "@type": {"pattern": "^IriTemplateMapping$"},
              "variable": {"pattern": "^book\\[\\]$"},
              "property": {"pattern": "^book$"},
              "required": false
            }
          ]
        }
      }
    }
  }
}
JSON
        ], 0));
    }
}
