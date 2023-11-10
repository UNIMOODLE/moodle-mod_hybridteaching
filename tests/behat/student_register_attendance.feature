@hybridteaching @hybridteaching_register_attendance @javascript
Feature: A student register his attendance in a session
  In order to register attendance in a session
  As a student
  I should be enabled to register attendance in sessions.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | One | teacher1@example.com |
      | student1 | Student | One | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Moodle Testing Hybrid Teaching | testhybridteaching | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | testhybridteaching | editingteacher |
      | student1 | testhybridteaching | student |
    And the following config values are set as admin:
      | enablemoodlenet | 0 | tool_moodlenet |
    And I log in as "teacher1"
    And I am on "Moodle Testing Hybrid Teaching" course homepage with editing mode on
    And I click on "Add an activity or resource" "button" in the "General" "section"
    And I click on "Add a new Hybrid teaching" "link" in the "Add an activity or resource" "dialogue"
    And I should see "Adding a new Hybrid teaching"
    And I set the following fields to these values:
      | Name | hybridteaching example |
      | Duration | 45 |
      | Student password | studentpassword |
    And I click on "Enable" "checkbox"
    And I press "Save and display"
    And I log out
        
  Scenario: Student register attendance in a session
    Given I log in as "student1"
    And I am on "Moodle Testing Hybrid Teaching" course homepage
    And I click on "hybridteaching example" "link" in the "hybridteaching example" activity
    When I set the following fields to these values:
      | qrpass | studentpassword |
    And I press "Sign attendance"
    Then I should see "Attendance entry registered succesfully"
    And I press "Finish attendance"