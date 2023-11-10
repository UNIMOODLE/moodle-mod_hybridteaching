@hybridteaching @hybridteaching_session @javascript
Feature: Add a hybridteaching session
  In order to add a session in a hybridteaching activity
  As a teacher
  I should be enabled to configure sessions in hybridteaching activities.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | One | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Moodle Testing Hybrid Teaching | testhybridteaching | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | testhybridteaching | editingteacher |
    And the following config values are set as admin:
      | enablemoodlenet | 0 | tool_moodlenet |
    And I log in as "teacher1"
    And I am on "Moodle Testing Hybrid Teaching" course homepage with editing mode on

  Scenario: The teacher create a hybridteaching activity and add a session
    Given I click on "Add an activity or resource" "button" in the "Topic 1" "section"
    And I click on "Add a new Hybrid teaching" "link" in the "Add an activity or resource" "dialogue"
    And I should see "Adding a new Hybrid teaching"
    And I set the following fields to these values:
      | Name | hybridteaching example |
      | Duration | 45 |
      | Student password | studentpass |
    And I click on "Use sessions scheduling" "checkbox"
    And I press "Save and display"
    And I click on "Schedule program" "link"
    And I click on "Add session" "link"
    When I set the following fields to these values:
      | Session name  | session example |
      | Duration | 45 |
      | Description | This is a hybridteaching session description |
    And I click on "Add" "button" in the "[data-fieldtype='submit']" "css_element"
    Then I should see "session example" in the "hybridteachingsessions" "table"