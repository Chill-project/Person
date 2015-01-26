Feature: history page
   An history page is present in person menu, which show a list of
   opening and closing operation.

   Background:
      Given I am logged as "center a_social" with password "password"
      Given a random person is selected
      Given I am on landing person page for the selected person

    Scenario: An entry is in the menu
       Then I should see "History"

    Scenario: An history page is accessible
       When I follow "History"
       Then the response status code should be 200
       And the url should match "/en/person/[0-9]*/history"
   