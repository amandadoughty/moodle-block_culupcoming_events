@block @cul @block_culupcoming_events @block_culupcoming_events_lookahead @javascript
Feature: CUL Upcoming events look ahead
  In order to be kept informed
  As an admin or teacher
  I can limit how far ahead the the events are shown

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following config values are set as admin:
      | lookahead | 15 | block_culupcoming_events |
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn editing mode on
    And I add the CUL Upcoming Events block
    And I press "Reset Dashboard for all users"
    And I log out

  Scenario: Lookahead can be set at block level
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Calendar" block
    And I create a calendar event with form data:
      | Type of event                      | course        |
      | Course                             | Course 1      |
      | Event title                        | Course Event  |
      | Repeat this event                  | 1             |
      | Repeat weekly, creating altogether | 10            |
    And I am on "Course 1" course homepage
    And I add the CUL Upcoming Events block
    And I configure the "block_culupcoming_events" block
    And I set the field "Number of days in the future to look for upcoming events" to "365"
    And I press "Save changes"
    And I log out
    Given I log in as "student1"
    # Confirm the feed is showing only 3 events.
    Then I should see "3" events in feed
    And I should see "No more events"
    And I am on "Course 1" course homepage
    Then I should see "7" events in feed
    # Confirm that scrolling adds remaining events
    And I scroll the events feed
    Then I should see "10" events in feed
    And I should see "No more events"
