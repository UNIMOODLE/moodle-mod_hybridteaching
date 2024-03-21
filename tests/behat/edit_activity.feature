@hybridteaching @hybridteaching_activity @javascript
Feature: Edit a hybridteaching activity in course page
  In order to edit a hybridteaching activity from a course
  As a teacher
  I should be enabled to add and edit activities from a course.

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
    And I am on "testhybridteaching" course homepage
    And I turn editing mode on

  Scenario: The teacher add and then edit the hybridteaching activity
    Given I click on "Add an activity or resource" "button" in the "Topic 1" "section"
    And I click on "Add a new Hybrid teaching" "link" in the "Add an activity or resource" "dialogue"
    And I should see "Adding a new Hybrid teaching"
    And I set the following fields to these values:
      | Name | hybridteaching activity to edit |
    And I press "Save and return to course"
    And I should see "hybridteaching activity to edit" in the "Topic 1" "section"
    And I open "hybridteaching activity to edit" actions menu  
    And I click on "Edit settings" "link" in the "hybridteaching activity to edit" activity  
    And I should see "Updating Hybrid teaching in Topic 1"
    When I set the following fields to these values:
      | Name | EDITED hybridteaching activity |
      | Description | I edited the hybridteaching activity description |
      | Duration | 120 |
      | Student password | editedpass |
      | Maximum grade | 100 |
      | Grade to pass | 50 |
      | Attendance for maximum score | 15 |
    And I press "Save and return to course"
    And I turn editing mode off
    Then I should see "EDITED hybridteaching activity" in the "Topic 1" "section"
