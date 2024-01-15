@hybridteaching @hybridteaching_activity @javascript
Feature: Configure a hybridteaching activity in course page
  In order to add and configure a hybridteaching activity to a course
  As a teacher
  I should be enabled to choose from a list of available activities.

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

  Scenario: The teacher add and configure an hybridteaching activity
    Given I click on "Add an activity or resource" "button" in the "Topic 1" "section"
    And I click on "Add a new Hybrid teaching" "link" in the "Add an activity or resource" "dialogue"
    And I should see "Adding a new Hybrid teaching"
    When I set the following fields to these values:
      | Name | Test hybridteaching activity 1 |
      | Description | This is a description about the Test hybridteaching activity 1 |
      | Duration | 60 |
      | Student password | studentpass |
      | Maximum grade | 10 |
      | Grade to pass | 5 |
      | Attendance for maximum score | 10 |
    And I press "Save and return to course"
    And I wait "1" seconds
    Then I should see "Test hybridteaching activity 1" in the "Topic 1" "section"