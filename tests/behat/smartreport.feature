@local @local_lionai_reports @javascript @accessibility
Feature: User can use AI to generate SQL request to DB, converted from humanreadable text.

  Background:
    Given I log in as "admin"
    And I navigate to "Plugins > LionAI Reports" in site administration
    And I click on ".fa-pencil" "css_element" in the ".form-password" "css_element"
    And I type the text "testing_token1" into the input with class "#id_s_local_lionai_reports_lionai_reports_apikey"
    And I click on "Save changes" "button"
    And I wait until the page is ready

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

  Scenario: Open LionAI Report page and try to sent prompt.
    Given I log in as "admin"
    And I navigate to "Reports > LionAI Reports" in site administration
    And I click on "LionAI Reports" "link"
    Then The page title should contain "LionAI Reports"
    And I click on "Add New Report" "button"
    And I wait until the page is ready
    And I wait until ".userprompt-textarea" "css_element" exists
    Then I should see "Add prompt:"

    When I type the text "Show me user" into the input with class ".userprompt-textarea"
    Then I click on "Send prompt" "button"
    And I wait until the page is ready
    Then I should see "Execute code"
    And I wait until "#id_queryresult_wrapper" "css_element" exists
    And I wait until the page is ready
    Then I click on "Execute code" "button"
    And I wait until the page is ready
    And I wait until "#id_queryresult" "css_element" exists
