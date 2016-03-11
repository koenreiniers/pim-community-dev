<?php

namespace Pim\Behat\Decorator\GridDecorator;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to manage the list of filters to be activated
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterManagerDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Activate a filter
     *
     * @param string $filterName
     */
    public function activateFilter($filterName)
    {
        $filter = $this->getFilterFromList($filterName)->find('css', 'input');
        $filter->check();
    }

    /**
     * Deactivate filter
     *
     * @param string $filterName
     */
    public function deactivateFilter($filterName)
    {
        $filter = $this->getFilterFromList($filterName)->find('css', 'input');
        $filter->uncheck();
    }

    /**
     * @param string $filterName
     *
     * @return bool
     */
    public function isFilterAvailable($filterName)
    {
        $filterElement = $this
            ->find('css', sprintf('label:contains("%s")', $filterName));

        return null !== $filterElement;
    }

    /**
     * Click on a filter in filter management list
     *
     * @param string $filterName
     *
     * @return NodeElement
     */
    protected function getFilterFromList($filterName)
    {
        return $this->spin(function () use ($filterName) {
            return $this
                ->find('css', sprintf('label:contains("%s")', $filterName));
        }, sprintf('Impossible to activate filter "%s"', $filterName));
    }
}
