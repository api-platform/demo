Feature: Manage books and their reviews
  In order to manage books and their reviews
  As a client software developer
  I need to be able to retrieve, create, update and delete them trough the API.

  Scenario: Create a book
    When I create a book
    Then I see a book

  Scenario: Retrieve the book list
    Given there is a book
    When I get a list of books
    Then I see a list of books

  Scenario: Throw errors when a post is invalid
    Given there is a book
    When I create a book with invalid data
    Then the request is invalid

  Scenario: Add a review
    Given there is a book
    When I create a review
    Then I see a review

  Scenario: Get reviews by book
    Given there is a book with a review
    When I get a list of reviews filtered by book
    Then I see a list of reviews
