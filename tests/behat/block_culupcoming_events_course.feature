@block  @block_culupcoming_events
Feature: Enable the CUL upcoming events block in a course
  In order to enable the calendar block in a course
  As a teacher
  I can view the event in the CUL upcoming events block

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | idnumber |
      | teacher1 | Teacher | 1 | teacher1@example.com | T1 |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |

  @javascript
  Scenario: View a global event in the calendar block
    Given I log in as "admin"
    And I create a calendar event with form data:
      | id_eventtype | Site |
      | id_name | My Site Event |
    And I am on "Course 1" course homepage with editing mode on
    And I add the "CUL Upcoming events" block
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    Then I should see "My Site Event" in the "Module events" "block"
