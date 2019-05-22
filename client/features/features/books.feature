# This is a demo feature applied to the demo books list component.
# Please remove them and create yours.
Feature:
  As any user,
  when I go to books list,
  I see a list of books.

  Scenario: I see a list of books
    When I go to the books list
    Then I see a list of books

  Scenario: I generate a book cover
    When I go to the books list
    And I click on a book id
    Then I see a book
    And I see a "Generate cover" button
    When I click on the "Generate cover" button
    And I wait 2 seconds
    Then I see a cover
