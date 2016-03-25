@javascript
Feature: Export products
  In order to use the enriched product data
  As a product manager
  I need to be able to export products to several channels

  Background:
    Given an "apparel" catalog configuration
    And the following products:
      | sku          | family  | categories                   | price                 | size   | color | manufacturer     | material | country_of_manufacture |
      | tshirt-white | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | cotton   | usa                    |
      | tshirt-black | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_L | black | american_apparel | cotton   | usa                    |
    And the following product values:
      | product      | attribute       | value                                | locale | scope     |
      | tshirt-white | name            | White t-shirt                        | en_US  |           |
      | tshirt-white | name            | White t-shirt                        | en_GB  |           |
      | tshirt-white | name            | T-shirt blanc                        | fr_FR  |           |
      | tshirt-white | name            | Weißes T-Shirt                       | de_DE  |           |
      | tshirt-white | image           | %fixtures%/SNKRS-1R.png              |        |           |
      | tshirt-white | cost            | 10 EUR, 20 USD, 30 GBP               |        |           |
      | tshirt-white | release_date    | 2016-10-12                           |        | tablet    |
      | tshirt-white | customer_rating | 2                                    |        | tablet    |
      | tshirt-white | handmade        | 1                                    |        |           |
      | tshirt-white | weight          | 5 KILOGRAM                           |        |           |
      | tshirt-white | number_in_stock | 10                                   |        | tablet    |
      | tshirt-white | description     | A stylish white t-shirt              | en_US  | tablet    |
      | tshirt-white | description     | Un T-shirt blanc élégant             | fr_FR  | ecommerce |
      | tshirt-white | description     | A really stylish white t-shirt       | en_US  | print     |
      | tshirt-black | name            | Black t-shirt                        | en_US  |           |
      | tshirt-black | name            | Black t-shirt                        | en_GB  |           |
      | tshirt-black | name            | T-shirt noir                         | fr_FR  |           |
      | tshirt-black | name            | Schwarzes T-Shirt                    | de_DE  |           |
      | tshirt-black | description     | Un T-shirt noir élégant              | fr_FR  | ecommerce |
      | tshirt-black | description     | Ein elegantes schwarzes T-Shirt      | de_DE  | ecommerce |
      | tshirt-black | description     | A really stylish black t-shirt       | en_US  | print     |
      | tshirt-black | description     | Ein sehr elegantes schwarzes T-Shirt | de_DE  | print     |

  Scenario: Successfully export products to multiple channels
    Given the following job "xlsx_tablet_product_export" configuration:
      | filePath | %tmp%/xlsx_tablet_product_export/xlsx_tablet_product_export.xlsx |
    When I launched the completeness calculator
    And I am logged in as "Julia"
    And I am on the "xlsx_tablet_product_export" export job page
    And I launch the export job
    And I wait for the "xlsx_tablet_product_export" job to finish
    Then exported xlsx file of "xlsx_tablet_product_export" should contain:
      | sku          | additional_colors | categories                 | color | cost-EUR | cost-GBP | cost-USD | country_of_manufacture | customer_rating-tablet | datasheet | description-en_GB-tablet | description-en_US-tablet | enabled | family  | groups | handmade | image                                 | legend-en_GB | legend-en_US | manufacturer     | material | name-en_GB    | name-en_US    | number_in_stock-tablet | price-EUR | price-GBP | price-USD | release_date-tablet | size   | thumbnail | washing_temperature | weight | weight-unit |
      | tshirt-white |                   | men_2013,men_2014,men_2015 | white | 10.00    | 20.00    | 30.00    | usa                    | 2                      |           |                          | A stylish white t-shirt  | 1       | tshirts |        | 1        | files/tshirt-white/image/SNKRS-1R.png |              |              | american_apparel | cotton   | White t-shirt | White t-shirt | 10                     | 10.00     | 9.00      | 15.00     | 2016-10-12          | size_M |           |                     | 5      | KILOGRAM    |
      | tshirt-black |                   | men_2013,men_2014,men_2015 | black |          |          |          | usa                    |                        |           |                          |                          | 1       | tshirts |        |          |                                       |              |              | american_apparel | cotton   | Black t-shirt | Black t-shirt |                        | 10.00     | 9.00      | 15.00     |                     | size_L |           |                     |        |             |
