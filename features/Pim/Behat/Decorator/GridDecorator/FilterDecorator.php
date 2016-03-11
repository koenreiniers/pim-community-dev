<?php

namespace Pim\Behat\Decorator\GridDecorator;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to manipulate activated filters
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Filters'         => 'div.filter-box',
        'Grid toolbar'    => 'div.grid-toolbar',
        'Manage filters'  => 'div.filter-list',
        'Select2 results' => '#select2-drop .select2-results',

    ];

    /**
     * Open the filter
     *
     * @param NodeElement $filter
     *
     * @throws \InvalidArgumentException
     */
    public function openFilter(NodeElement $filter)
    {
        $element = $this->spin(function () use ($filter) {
            return $filter->find('css', 'button')->getParent();
        }, 'Impossible to open filter or maybe its type is not yet implemented');

        $element->click();
    }

    /**
     * Open/close the filter list
     */
    public function clickManageFilters()
    {
        $filterList = $this->spin(function () {
            return $this->find('css', '#add-filter-button');
        }, 'Impossible to find filter list');

        $filterList->click();
    }

    /**
     * Get grid filter from label name
     *
     * @param string $filterName
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    public function getFilter($filterName)
    {
        $filter = $this->spin(function () use ($filterName) {
            if (strtolower($filterName) === 'channel') {
                $filter = $this->find('css', $this->selectors['Grid toolbar'])->find('css', 'div.filter-item');
            } else {
                $filter = $this->find('css', sprintf('div.filter-item:contains("%s")', $filterName));
            }

            return $filter;
        }, sprintf('Couldn\'t find a filter with name "%s"', $filterName));

        return $filter;
    }

    /**
     * @param string               $filterName The name of the filter
     * @param string               $value      The value to filter by
     * @param bool|string          $operator   If false, no operator will be selected
     * @param DriverInterface|null $driver     Required to filter by multiple choices
     *
     * @throws \InvalidArgumentException
     */
    public function filterBy($filterName, $value, $operator = false, DriverInterface $driver = null)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        if ($elt = $filter->find('css', 'select')) {
            if ($elt->getText() === "between not between more than less than is empty") {
                $this->filterByDate($filter, $value, $operator);
            } elseif ($elt->getParent()->find('css', 'button.ui-multiselect')) {
                if (!$driver || !$driver instanceof Selenium2Driver) {
                    throw new \InvalidArgumentException('Selenium2Driver is required to filter by a choice filter');
                }
                $values = explode(',', $value);

                foreach ($values as $value) {
                    $driver->executeScript(
                        sprintf(
                            "$('.ui-multiselect-menu:visible input[title=\"%s\"]').click().trigger('click');",
                            $value
                        )
                    );
                    sleep(1);
                }

                // Uncheck the 'All' option
                if (!in_array('All', $values)) {
                    $driver->executeScript(
                        "var all = $('.ui-multiselect-menu:visible input[title=\"All\"]');" .
                        "if (all.length && all.is(':checked')) { all.click().trigger('click'); }"
                    );
                }
            }
        } elseif ($elt = $filter->find('css', 'div.filter-criteria')) {
            $results = $this->find('css', $this->selectors['Select2 results']);
            $select2 = $filter->find('css', '.select2-input');

            if (false !== $operator) {
                $filter->find('css', 'button.dropdown-toggle')->click();
                $filter->find('css', sprintf('[data-value="%s"]', $operator))->click();
            }

            if (null !== $results && null !== $select2) {
                if (in_array($value, ['empty', 'is empty'])) {
                    // Allow passing 'empty' as value too (for backwards compability with existing scenarios)
                    $filter->find('css', 'button.dropdown-toggle')->click();
                    $filter->find('css', '[data-value="empty"]')->click();
                } else {
                    $values = explode(',', $value);
                    foreach ($values as $value) {
                        $driver->getWebDriverSession()
                            ->element('xpath', $select2->getXpath())
                            ->postValue(['value' => [$value]]);
                        sleep(2);
                        $results->find('css', 'li')->click();
                        sleep(2);
                    }
                }
            } elseif ($value !== false) {
                $elt->fillField('value', $value);
            }

            $filter->find('css', 'button.filter-update')->click();
        } else {
            throw new \InvalidArgumentException(
                sprintf('Filtering by "%s" is not yet implemented"', $filterName)
            );
        }
    }


    /**
     * Make sure a filter is visible
     *
     * @param string $filterName
     *
     * @throws \InvalidArgumentException
     */
    public function assertFilterVisible($filterName)
    {
        if (!$this->getFilter($filterName)->isVisible()) {
            throw new \InvalidArgumentException(
                sprintf('Filter "%s" is not visible', $filterName)
            );
        }
    }

    /**
     * Make sure a filter is visible
     *
     * @param string $filterName
     *
     * @throws \InvalidArgumentException
     */
    public function assertFilterNotVisible($filterName)
    {
        try {
            $isVisible = $this->getFilter($filterName)->isVisible();
        } catch (TimeoutException $e) {
            $isVisible = false;
        }

        if (true === $isVisible) {
            throw new \InvalidArgumentException(
                sprintf('Filter "%s" is not visible', $filterName)
            );
        }
    }

    /**
     * @param string $filterName The name of the price filter
     * @param string $action     Type of filtering (>, >=, etc.)
     * @param number $value      Value to filter
     * @param string $currency   Currency on which to filter
     */
    public function filterPerPrice($filterName, $action, $value, $currency)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        if (null !== $value) {
            $criteriaElt = $filter->find('css', 'div.filter-criteria');
            $criteriaElt->fillField('value', $value);
        }

        $buttons        = $filter->findAll('css', '.currencyfilter button.dropdown-toggle');
        $actionButton   = array_shift($buttons);
        $currencyButton = array_shift($buttons);

        // Open the dropdown menu with currency list and click on $currency line
        $currencyButton->click();
        $currencyButton->getParent()->find('css', sprintf('ul a:contains("%s")', $currency))->click();

        // Open the dropdown menu with action list and click on $action line
        $actionButton->click();
        $actionButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $action))->click();

        $filter->find('css', 'button.filter-update')->click();
    }

    /**
     * @param string $filterName The name of the metric filter
     * @param string $action     Type of filtering (>, >=, etc.)
     * @param float  $value      Value to filter
     * @param string $unit       Unit on which to filter
     */
    public function filterPerMetric($filterName, $action, $value, $unit)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        $criteriaElt = $filter->find('css', 'div.filter-criteria');
        $criteriaElt->fillField('value', $value);

        $buttons      = $filter->findAll('css', '.metricfilter button.dropdown-toggle');
        $actionButton = array_shift($buttons);
        $unitButton   = array_shift($buttons);

        // Open the dropdown menu with unit list and click on $unit line
        $unitButton->click();
        $unitButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $unit))->click();

        // Open the dropdown menu with action list and click on $action line
        $actionButton->click();
        $actionButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $action))->click();

        $filter->find('css', 'button.filter-update')->click();
    }

    /**
     * @param string $filterName The name of the number filter
     * @param string $action     Type of filtering (>, >=, etc.)
     * @param float  $value      Value to filter
     */
    public function filterPerNumber($filterName, $action, $value)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        $criteriaElt = $filter->find('css', 'div.filter-criteria');
        $criteriaElt->fillField('value', $value);

        $buttons      = $filter->findAll('css', '.filter-criteria button.dropdown-toggle');
        $actionButton = array_shift($buttons);

        // Open the dropdown menu with action list and click on $action line
        $actionButton->click();
        $actionButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $action))->click();

        $filter->find('css', 'button.filter-update')->click();
    }


    /**
     * @param NodeElement $filter
     * @param string      $value
     * @param string      $operator
     */
    protected function filterByDate($filter, $value, $operator)
    {
        $elt = $filter->find('css', 'select');
        if ('empty' === $operator) {
            $elt->selectOption('is empty');
        } else {
            $elt->selectOption($operator);
        }

        $filter->find('css', 'button.filter-update')->click();
    }
}
