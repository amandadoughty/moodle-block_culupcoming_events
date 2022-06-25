@block @cul @block_culupcoming_events @block_culupcoming_events_scroll @javascript
Feature: CUL Upcoming events block scroll
  In order to be kept informed
  As a user
  I can scroll the CUL Upcoming events block to view more events

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
    And I log in as "admin"
    And I create a calendar event with form data:
      | id_eventtype | Site          |
      | id_name      | My Site Event |
      | id_repeat    | 1             |
      | id_repeats   | 10            |
    And I navigate to "Appearance > Default Dashboard page" in site administration
    And I turn editing mode on
    And I add the CUL Upcoming Events block
    And I press "Reset Dashboard for all users"
    And I log out

  Scenario: Scrolling loads more events
    Given I log in as "student1"
    # Confirm the feed is showing only 7 events.
    Then I should see "7" events in feed
    # Confirm that scrolling adds remaining events
    And I scroll the events feed
    Then I should see "10" events in feed
    And I should see "No more events"
