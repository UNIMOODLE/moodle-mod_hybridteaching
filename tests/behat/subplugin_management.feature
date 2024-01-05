@hybridteaching @hybridteaching_subplugin_management @javascript
Feature: An admin configure a subplugin in the hybridteaching plugin
  In order to configure subplugins in the hybridteaching plugin
  As an admin
  I should be enabled to configure the plugin in the site administration menu.

  Scenario: Configure subplugins in the hybridteaching plugin
    Given I log in as "admin"
    And I navigate to "Plugins > Manage videoconference settings" in site administration
    And I select "BBB" from the "jump" singleselect
    When I set the following fields to these values:
      | Config name  | videoconference configuration example |
      | Url del servidor BigBlueButton  | http://example.com/ |
      | Clave secreta de BigBlueButton | secretkey |
    And I click on "Course categories" "button"
    And I click on "checkboxcategory-1" "checkbox"
    And I click on "Save changes" "button"
    And I click on "Add setting" "button"
    And I should see "config created successfully"
    Then I should see "videoconference configuration example" in the "hybridteachingpluginsconfigs" "table"
    And I click on "Disable" "link"
    And I click on "Enable" "link"
