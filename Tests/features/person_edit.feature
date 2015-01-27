Feature: person edition
    As an user
    The user could be able to reach a form to edit the person details (name, ...)
    Any edition should update the person entity

    Background:
        Given I am logged as "center a_social" with password "password"
        And a random person is selected
        And I am on landing person page for the selected person

    Scenario: A "modify" link is present
        Then I should see "Edit"

    Scenario: The edit form is accessible
        Given I follow "Edit"
        Then the response status code should be 200
        And the url should match "/en/person/[0-9]*/general/edit"

    Scenario Outline: Edit the field with valid text inputs
        Given I follow "Edit"
        And I fill in "<label>" with "<new value>"
        And I press "Submit"
        Then the url should match "/en/person/[0-9]*/general"
        Then the selected person value "<value>" should be "<new value>"

        Examples:
            | label         |  new value                | value                 |
            | First name    |  random firstname         | firstname             |
            | Last name     |  random lastname          | lastname              |
            | Place of birth|  random place of birth    | placeOfBirth          |
            | Phonenumber   |   0123456789              | phoneNumber           |
            | Email addresses | kmfdqfdkqlm             | email |
            | Memo          |   jklmfdjqkm mlfdqm       | memo |

    Scenario Outline: Edit the fields with select inputs
        Given I follow "Edit"
        And I select "<new value>" from "<label>"
        And I press "Submit"
        Then the url should match "/en/person/[0-9]*/general$"
        Then the selected person value "<value>" should be "<new value>"

        Examples:
            | label         |  new value                | value                 |
            | Nationality   |  Belgium             | nationality.name |
            | Country of birth | France            | countryOfBirth.name |

    Scenario: too long names should show an error
        Given I follow "Edit"
        And I fill in "First name" with: 
            """
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse molestie at enim id auctor. Vivamus malesuada elit ipsum, ac mollis ex facilisis sit amet. Phasellus accumsan, quam ut aliquet accumsan, augue ligula consequat erat, condimentum iaculis orci magna egestas eros. In vel blandit sapien. Duis ut dui vitae tortor iaculis malesuada vitae vitae lorem. Morbi efficitur dolor orci, a rhoncus urna blandit quis. Aenean at placerat dui, ut tincidunt nulla. In ultricies tempus ligula ac rutrum. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Fusce urna nibh, placerat vel auctor sed, maximus quis magna. Vivamus quam ante, consectetur vel feugiat quis, aliquet id ante. Integer gravida erat dignissim ante commodo mollis. Donec imperdiet mauris elit, nec blandit dolor feugiat ut. Proin iaculis enim ut tortor pretium commodo. Etiam aliquet hendrerit dolor sed fringilla. Vestibulum facilisis nibh tincidunt dui egestas, vitae congue mi imperdiet. Duis vulputate ultricies lectus id cursus. Fusce bibendum sem dignissim, bibendum purus quis, mollis ex. Cras ac est justo. Duis congue mattis ipsum, vitae sagittis justo dictum sit amet. Duis aliquam pharetra sem, non laoreet ante laoreet ac. Mauris ornare mi tempus rutrum consequat. 
            """
        And I press "Submit"
        Then I should see ".error" elements
        And the url should match "/en/person/[0-9]/general/edit$"
            