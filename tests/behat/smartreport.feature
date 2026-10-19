@local @local_lionai_reports @javascript @accessibility
Feature: User can use AI to generate SQL request to DB, converted from humanreadable text.

  Background:
    Given the following config values are set as admin:
      | lionai_reports_apikey | testing_token1 | local_lionai_reports |
    And I log in as "admin"

  Scenario: Open LionAI Report page and try to sent useless prompt.
    Given I log in as "admin"
    And I navigate to "Reports > LionAI Reports" in site administration
    And I click on "LionAI Reports" "link"
    Then The page title should contain "LionAI Reports"
    And I click on "Add New Report" "button"
    And I wait until the page is ready
    And I wait until ".userprompt-textarea" "css_element" exists
    Then I should see "Add prompt:"

    When I type the text "Show me the sense of life" into the input with class ".userprompt-textarea"
    Then I click on "Send prompt" "button"
    Then I should see "Does not look like correct code. Use carefully."

  Scenario: Execute the SQL of a report and see the preview.
    Given a LionAI report named "Admin report" exists with the query "SELECT id, username FROM {user} WHERE username = 'admin'"
    And I log in as "admin"
    When I open the LionAI report named "Admin report"
    And I wait until ".userprompt-textarea" "css_element" exists
    And I click on "Execute code" "button"
    And I wait until "#id_queryresult_wrapper" "css_element" exists
    Then I should see "username" in the "#id_queryresult_wrapper" "css_element"
    And I should see "admin" in the "#id_queryresult_wrapper" "css_element"
