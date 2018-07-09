Feature: Manage books and their reviews
  In order to manage books and their reviews
  As a client software developer
  I need to be able to retrieve, create, update and delete them trough the API.

  @createSchema
  Scenario: Create a book
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/books" with body:
    """
    {
      "isbn": "9781782164104",
      "title": "Persistence in PHP with the Doctrine ORM",
      "description": "This book is designed for PHP developers and architects who want to modernize their skills through better understanding of Persistence and ORM.",
      "author": "Kévin Dunglas",
      "publicationDate": "2013-12-01"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "@context": "/contexts/Book",
      "@id": "/books/1",
      "@type": "http://schema.org/Book",
      "isbn": "9781782164104",
      "title": "Persistence in PHP with the Doctrine ORM",
      "description": "This book is designed for PHP developers and architects who want to modernize their skills through better understanding of Persistence and ORM.",
      "author": "K\u00e9vin Dunglas",
      "publicationDate": "2013-12-01T00:00:00+00:00",
      "reviews": []
    }
    """

  Scenario: Retrieve the book list
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/books"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "@context": "/contexts/Book",
      "@id": "/books",
      "@type": "hydra:Collection",
      "hydra:member": [
        {
          "@id": "/books/1",
          "@type": "http://schema.org/Book",
          "isbn": "9781782164104",
          "title": "Persistence in PHP with the Doctrine ORM",
          "description": "This book is designed for PHP developers and architects who want to modernize their skills through better understanding of Persistence and ORM.",
          "author": "K\u00e9vin Dunglas",
          "publicationDate": "2013-12-01T00:00:00+00:00",
          "reviews": []
        }
      ],
      "hydra:totalItems": 1,
      "hydra:search": {
        "@type": "hydra:IriTemplate",
        "hydra:template": "/books{?properties[]}",
        "hydra:variableRepresentation": "BasicRepresentation",
        "hydra:mapping": [
            {
                "@type": "IriTemplateMapping",
                "variable": "properties[]",
                "property": null,
                "required": false
            }
        ]
      }
    }
    """

  Scenario: Throw errors when a post is invalid
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/books" with body:
    """
    {
      "isbn": "1312",
      "title": "",
      "description": "Yo!",
      "author": "Me!",
      "publicationDate": "2016-01-01"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be equal to:
    """
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
    """

    Scenario: Add a review
    When I add "Content-Type" header equal to "application/ld+json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/reviews" with body:
    """
    {
      "rating": 5,
      "body": "Must have!",
      "author": "Foo Bar",
      "publicationDate": "2016-01-01",
      "book": "/books/1"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "@context": "/contexts/Review",
      "@id": "/reviews/1",
      "@type": "http://schema.org/Review",
      "body": "Must have!",
      "rating": 5,
      "letter": null,
      "book": {
        "@id": "/books/1",
        "@type": "http://schema.org/Book",
        "title": "Persistence in PHP with the Doctrine ORM"
      },
      "author": "Foo Bar",
      "publicationDate": "2016-01-01T00:00:00+00:00"
    }
    """

    Scenario: Get reviews by book
    When I add "Accept" header equal to "application/ld+json"
    And I send a "GET" request to "/reviews?book=/books/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/ld+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "@context": "/contexts/Review",
      "@id": "/reviews",
      "@type": "hydra:Collection",
      "hydra:member": [
        {
          "@id": "/reviews/1",
          "@type": "http://schema.org/Review",
          "body": "Must have!",
          "rating": 5,
          "letter": null,
          "book": {
            "@id": "/books/1",
            "@type": "http://schema.org/Book",
            "title": "Persistence in PHP with the Doctrine ORM"
          },
          "author": "Foo Bar",
          "publicationDate": "2016-01-01T00:00:00+00:00"
        }
      ],
      "hydra:totalItems": 1,
      "hydra:view": {
        "@id": "/reviews?book=%2Fbooks%2F1",
        "@type": "hydra:PartialCollectionView"
      },
      "hydra:search": {
        "@type": "hydra:IriTemplate",
        "hydra:template": "/reviews{?book,book[]}",
        "hydra:variableRepresentation": "BasicRepresentation",
        "hydra:mapping": [
          {
            "@type": "IriTemplateMapping",
            "variable": "book",
            "property": "book",
            "required": false
          },
          {
            "@type": "IriTemplateMapping",
            "variable": "book[]",
            "property": "book",
            "required": false
          }
        ]
      }
    }
    """
