Feature: Update user preferences
  In order for users to be able to choose their preferences
  As a developer
  I need to synchronize user preferences with the catalog configuration

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "admin"

  @javascript
  Scenario: Successfully delete a tree used by a user
    Given I edit the "Julia" user
    And I visit the "Additional data" tab
    Then I should see "Default tree"
    And I should see "2013_collection"
    When I edit the "tablet" channel
    And I change the "Category tree" to "2014 collection"
    And I save the channel
    And I edit the "2013_collection" category
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "Julia" user
    And I visit the "Additional data" tab
    Then I should see "Default tree"
    And I should see "2014_collection"
    And I should not see "2013_collection"

  @javascript
  Scenario: Successfully delete a channel used by a user
    Given I edit the "Peter" user
    And I visit the "Additional data" tab
    Then I should see "Catalog scope"
    And I should see "print"
    When I edit the "print" channel
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "Peter" user
    And I visit the "Additional data" tab
    Then I should see "Catalog scope"
    And I should see "ecommerce"
    And I should not see "print"

  @javascript
  Scenario: Successfully disable a locale used by a user
    Given I edit the "Julia" user
    And I select "fr_FR" from "Catalog locale"
    And I save the user
    When I visit the "Additional data" tab
    Then I should see "Catalog locale"
    And I should see "fr_FR"
    When I edit the "ecommerce" channel
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "Julia" user
    And I visit the "Additional data" tab
    Then I should see "Catalog locale"
    And I should see "en_US"
    And I should not see "fr_FR"
