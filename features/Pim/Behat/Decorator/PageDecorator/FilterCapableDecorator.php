<?php

namespace Pim\Behat\Decorator\PageDecorator;

use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to handle the grid of a page
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterCapableDecorator extends ElementDecorator
{
    const FILTER_CONTAINS         = 1;
    const FILTER_DOES_NOT_CONTAIN = 2;
    const FILTER_IS_EQUAL_TO      = 3;
    const FILTER_STARTS_WITH      = 4;
    const FILTER_ENDS_WITH        = 5;
    const FILTER_IS_EMPTY         = 'empty';
    const FILTER_IN_LIST          = 'in';

    protected $selectors = [
        'Filters' => 'div.filter-box',
        'Filter list'  => 'div.filter-list',
    ];

    /** @var array */
    protected $decorators = [
        'Pim\Behat\Decorator\GridDecorator\FilterDecorator',
        'Pim\Behat\Decorator\GridDecorator\FilterManagerDecorator',
    ];

    public function getFilterManager()
    {
        return $this->decorate($this->find('css', $this->selectors['Filter list']), [$this->decorators[1]]);
    }

    public function getFilters()
    {
        return $this->decorate($this->find('css', $this->selectors['Filters']), [$this->decorators[0]]);
    }
}
