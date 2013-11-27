@javascript
Feature: Family creation
  In order to provide a new family for a new type of product
  As a user
  I need to be able to create a family

  Background:
    Given a "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Succesfully create a family
    Given I am on the families page
    When I create a new family
    Then I should see the Code field
    And I fill in the following information in the popin:
      | Code | CAR |
    And I press the "Save" button
    Then I should be on the "CAR" family page
    And I should see "Edit family - [CAR]"

  Scenario: Fail to create a family with an empty or invalid code
    Given I am on the families page
    When I create a new family
    And I press the "Save" button
    Then I should see validation error "This value should not be blank."
    And I fill in the following information in the popin:
      | Code | =( |
    And I press the "Save" button
    Then I should see validation error "Family code may contain only letters, numbers and underscores"
  
  Scenario: Fail to create a family with an already used code
    Given the following families:
      | code |
      | BOAT |
    When I am on the families page
    And I create a new family
    And I fill in the following information in the popin:
      | Code | BOAT |
    And I press the "Save" button
    Then I should see validation error "This value is already used."
