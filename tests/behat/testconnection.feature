@local @local_lionai_reports @javascript
Feature: Admin can test the connection to the LionAI service from the plugin settings.

  Background:
    Given I log in as "admin"

  Scenario: Test connection with an invalid URL.
    Given the following config values are set as admin:
      | lionai_reports_apikey | testing_token1 | local_lionai_reports |
    And I navigate to "Plugins > LionAI Reports" in site administration
    When I set the field "s_local_lionai_reports_lionai_reports_apiurl" to "not-a-url"
    And I press "Test connection"
    Then I should see "is not a valid URL" in the "#local_lionai_reports_testconnection_result" "css_element"
    And "Test connection" "button" should be visible

  Scenario: Test connection without an API key.
    Given the following config values are set as admin:
      | lionai_reports_apikey |  | local_lionai_reports |
    And I navigate to "Plugins > LionAI Reports" in site administration
    When I set the field "s_local_lionai_reports_lionai_reports_apiurl" to "https://example.invalid/v1"
    And I press "Test connection"
    Then I should see "Please enter an API key." in the "#local_lionai_reports_testconnection_result" "css_element"
