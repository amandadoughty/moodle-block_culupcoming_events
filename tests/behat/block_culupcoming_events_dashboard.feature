@block @block_culupcoming_events
Feature: View a upcoming site event on the dashboard
  In order to view a site event
  As a student
  I can view the event in the CUL upcoming events block
  
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | idnumber |
      | student1 | Student | 1 | student1@example.com | S1 |

  @javascript
  Scenario: View a global event in the CUL Upcoming events block on the dashboard
    Given I log in as "admin"
    And I create a calendar event with form data:
      | id_eventtype | Site |
      | id_name | My Site Event |
    And I log out
    When I log in as "student1"
    And I am on homepage
    And I press "Customise this page"
    And I add the "CUL Upcoming events" block
    Then I should see "My Site Event" in the "CUL Upcoming events" "block"
