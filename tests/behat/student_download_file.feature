@hybridteaching @hybridteaching_download_file @_file_upload @javascript
Feature: A student download a file from a hybridteaching session
  In order to download a file
  As a student
  I should be enabled to download files in hybridteaching sessions.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | One | teacher1@example.com |
      | student1 | Student | One | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | testhybridteaching | testhybridteaching | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | testhybridteaching | editingteacher |
      | student1 | testhybridteaching | student |
    And the following config values are set as admin:
      | enablemoodlenet | 0 | tool_moodlenet |
    And I log in as "teacher1"
    And I am on "testhybridteaching" course homepage
    And I turn editing mode on
    And I click on "Add an activity or resource" "button" in the "General" "section"
    And I click on "Add a new Hybrid teaching" "link" in the "Add an activity or resource" "dialogue"
    And I should see "Adding a new Hybrid teaching"
    And I set the following fields to these values:
      | Name | hybridteaching example |
    And I click on "Use sessions scheduling" "checkbox"
    And I press "Save and display"
    And I click on "Schedule program" "link"
    And I click on "Add session" "link"
    When I set the following fields to these values:
      | Session name  | session example |
      | Duration | 45 |
    And I click on "Add..." "link"
    And I click on "URL downloader" "link"
    And I set the following fields to these values:
    | URL: | https://www.isyc.com/wp-content/uploads/2018/03/logo_isyc.png |
    And I click on ".fp-login-submit" "css_element"
    And I click on "logo_isyc.png" "link"
    And I click on "Select this file" "button"
    Then I click on "Add" "button" in the "[data-fieldtype='submit']" "css_element"
    And I log out

  Scenario: Student download a file
    Given I log in as "student1"
    And I am on "testhybridteaching" course homepage
    And I click on "hybridteaching example" "link" in the "hybridteaching example" activity
    When I click on "More" if it exists otherwise "Sessions"
    And I should see "logo_isyc.png" in the "hybridteachingsessions" "table"
    Then I click on "logo_isyc.png" "link"
    And I wait "2" seconds