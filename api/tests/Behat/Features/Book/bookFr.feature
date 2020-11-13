Feature:
  Scenario: Call a not found route
    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/v1/not-found-route"
    Then the response status HTTP should be HTTP_NOT_FOUND

  Scenario: test ConstraintViolationList
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/books" with body:
"""
{
  "isbn": "plop",
  "title": "Clean Code",
  "description": "A Handbook of Agile Software Craftsmanship",
  "author": "Robert C. Martin",
  "publicationDate": "2008-08-01T23:11:31",
  "cover": "https://productimages.worldofbooks.com/0132350882.jpg"
}
"""
    Then the response status HTTP should be HTTP_BAD_REQUEST
    And the JSON nodes should be equal to:
    | hydra:description | isbn: This value is neither a valid ISBN-10 nor a valid ISBN-13.|
  Scenario: Successfully add book
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "POST" request to "/books" with body:
"""
{
  "isbn": "9780132350884",
  "title": "Clean Code",
  "description": "A Handbook of Agile Software Craftsmanship",
  "author": "Robert C. Martin",
  "publicationDate": "2008-08-01T23:11:31",
  "cover": "https://productimages.worldofbooks.com/0132350882.jpg"
}
"""
    And the response status HTTP should be HTTP_CREATED
    And the JSON nodes should be equal to:
      | isbn   | 9780132350884    |
      | title  | Clean Code       |
      | author | Robert C. Martin |
  Scenario: update last book
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "PUT" project request to "$id" where id is "@id" from last request with body:
"""
{
  "isbn": "232600227X",
  "title": "Coder proprement",
  "description": "Le point sur les pratiques pour nettoyer un code.",
  "author": "Robert C. Martin",
  "publicationDate": "2019-04-05T23:11:31",
  "cover": "https://static.fnac-static.com/multimedia/Images/FR/NR/73/22/a7/10953331/1540-1/tsp20190510112036/Coder-proprement.jpg"
}
"""
    Then the response status HTTP should be HTTP_OK
    And the JSON nodes should be equal to:
      | isbn   | 232600227X       |
      | title  | Coder proprement |
      | author | Robert C. Martin |

  Scenario: get last book
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "GET" project request to "$id" where id is "@id" from last request
    Then the response status HTTP should be HTTP_OK
    And print last JSON response

  Scenario: try delete book
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/ld+json"
    And I send a "DELETE" project request to "$id" where id is "@id" from last request
    Then the response status HTTP should be HTTP_UNAUTHORIZED
    And print last JSON response
