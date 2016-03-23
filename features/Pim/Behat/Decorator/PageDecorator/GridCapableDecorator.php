<?php

namespace Pim\Behat\Decorator\PageDecorator;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to handle the grid of a page
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GridCapableDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /** @var array Selectors to ease find */
    protected $selectors = [
        'Dialog grid' => '.modal',
        'Grid'        => 'table.grid',
    ];

    /** @var array */
    protected $decorators = [
        'Pim\Behat\Decorator\GridDecorator\ViewDecorator',
        'Pim\Behat\Decorator\GridDecorator\DataDecorator',
        'Pim\Behat\Decorator\GridDecorator\PaginationDecorator',
        'Pim\Behat\Decorator\GridDecorator\ActionDecorator',
    ];

    /**
     * Returns the currently visible grid, if there is one
     *
     * @return NodeElement
     */
    public function getCurrentGrid()
    {
        $grid = $this->spin(
            function () {
                $modal = $this->find('css', $this->selectors['Dialog grid']);
                if (null !== $modal && $modal->isVisible()) {
                    return $modal->find('css', $this->selectors['Grid']);
                }

                return $this->find('css', $this->selectors['Grid']);
            },
            'No visible grid found'
        );

        return $this->decorate($grid->getParent()->getParent()->getParent()->getParent(), $this->decorators);
    }

    public function getViews()
    {
        return null;
    }
}
