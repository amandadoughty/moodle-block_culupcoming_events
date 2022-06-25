@block @cul @block_culupcoming_events @block_culupcoming_events_reload @javascript
Feature: CUL Upcoming events block automatic reload
  In order to be kept informed
  As a user
  I see new events loaded in the CUL Upcoming events block

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
    And I log in as "admin"
    And I create a calendar event with form data:
      | Type of event | Site          |
      | Event title   | My Site Event |
      | Date          | ## +2 days ## |
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn editing mode on
    And I add the CUL Upcoming Events block
    And I press "Reset Dashboard for all users"
    And I log out

  Scenario: Feed refreshes when reloaded
    Given I log in as "student1"
    And I am on homepage
    Then I should see "1" events in feed
    And the following "events" exist:
      | name        | eventtype | course |
      | C1 event 3  | course    | C1    |
    Then I should see "1" events in feed
    And I click on "Refresh Feed" "link"
    Then I should see "2" events in feed
    And "C1 event 3" "list_item" should appear before "My Site Event" "list_item"

  Scenario: Feed refreshes every 5 mins
    Given I log in as "student1"
    And I am on homepage
    Then I should see "1" events in feed
    And the following "events" exist:
      | name | eventtype | course |
      | C1 event 3 | course | C1 |
    Then I should see "1" events in feed
    And I wait "310" seconds
    Then I should see "2" events in feed
    And "C1 event 3" "list_item" should appear before "My Site Event" "list_item"
