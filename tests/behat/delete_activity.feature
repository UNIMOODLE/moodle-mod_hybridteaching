@hybridteaching @javascript
Feature: Delete an hybridteaching activity from a course page
  In order to delete an hybridteaching activity from a course
  As a teacher
  I should be enabled to add and delete activities from a course.

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

  Scenario: The teacher add and delete the hybridteaching activity
    Given I click on "Add an activity or resource" "button" in the "Topic 1" "section"
    When I click on "Add a new Hybrid teaching" "link" in the "Add an activity or resource" "dialogue"
    Then I should see "Adding a new Hybrid teaching"
    When I set the following fields to these values:
      | Name | hybridteaching activity to delete |
    And I press "Save and return to course"
    Then I should see "hybridteaching activity to delete" in the "Topic 1" "section"
    And I delete "hybridteaching activity to delete" activity
    And I turn editing mode off
    Then I should not see "hybridteaching activity to delete" in the "courseindex-content" "region"