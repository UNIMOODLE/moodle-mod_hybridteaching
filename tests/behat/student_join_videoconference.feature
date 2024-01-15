@hybridteaching @hybridteaching_join_videoconference @javascript
Feature: A student join a videoconference in a hybridteaching activity
  In order to join a videoconference
  As a student
  I should be enabled to press the join videoconference button and be redirect to a videoconferences page.

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
    When I log in as "admin"
    And I wait "2" seconds
    And I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "Manage videoconference settings" "link"
    And I wait "1" seconds
    And I select "Zoom" from the "jump" singleselect
    And I set the following fields to these values:
      | Config name  | Videoconference subplugin test |
      | Zoom account ID | *YOUR_ZOOM_ACCOUNT_ID* |
      | Zoom client ID | *YOUR_ZOOM_CLIENT_ID* |
      | Zoom client secret | *YOUR_ZOOM_CLIENT_SECRET* |
      | Zoom license email | *YOUR_ZOOM_LICENSE_EMAIL* |
    And I click on "Course categories" "button"
    And I click on "checkboxcategory-1" "checkbox"
    And I click on "Save changes" "button"
    And I click on "Add setting" "button"
    And I should see "config created successfully"
    And I should see "Videoconference subplugin test" in the "hybridteachingpluginsconfigs" "table"
    And I log out
    And I log in as "teacher1"
    And I am on "testhybridteaching" course homepage with editing mode on
    And I click on "Add an activity or resource" "button" in the "General" "section"
    And I click on "Add a new Hybrid teaching" "link" in the "Add an activity or resource" "dialogue"
    And I should see "Adding a new Hybrid teaching"
    And I set the following fields to these values:
      | Name | hybridteaching example |
    And I select "Videoconference subplugin test (zoom)" from the "typevc" singleselect
    And I press "Save and display"
    And I wait "1" seconds
    And I click on "Join videoconference" "button"
    And I wait "1" seconds
    And I switch to the main window
    And I wait "1" seconds
    And I switch to a second window
    And I click on "onetrust-reject-all-handler" "button"
    And I wait "2" seconds
    And I click on "Iniciar reunión" "button"
    And I wait "2" seconds
    And I click on "Únase desde su navegador" "button"
    And I wait "5" seconds
    Then I switch to the main window
    And I log out

  Scenario: Student join a videoconference
    Given I log in as "student1"
    And I am on "testhybridteaching" course homepage
    When I click on "hybridteaching example" "link" in the "hybridteaching example" activity
    And I wait "1" seconds
    Then I press "Join videoconference"
    And I wait "5" seconds