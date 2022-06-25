@block @cul @block_culupcoming_events @block_culupcoming_events_no_javascript
Feature: CUL Upcoming events block with no JS
  In order to be kept informed
  As a user
  I can use the CUL Upcoming events block with JS disabled

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
    And I log in as "admin"
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn editing mode on
    And I add the CUL Upcoming Events block
    And I press "Reset Dashboard for all users"
    And I press "Continue"
    And I follow "New event"
    And I set the following fields to these values:
      | Type of event                      | Site                |
      | Event title                        | Another site event  |
      | Repeat this event                  | 1                   |
      | Repeat weekly, creating altogether | 10                  |
    And I press "Save changes"
    And I log out

  Scenario: events are paged with JS disabled
    Given I log in as "student1"
    # Confirm the feed is showing only 7 events.
    Then I should see "7" events in feed
    # Confirm that paging loads remaining events
    And I follow "Later"
    Then I should see "3" events in feed
    And I follow "Sooner"
    Then I should see "7" events in feed
