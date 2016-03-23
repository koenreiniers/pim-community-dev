<?php

namespace Pim\Behat\Decorator\GridDecorator;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to add pagination features to an element
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViewDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Grid container'    => '.grid-container',
        'Grid'              => 'table.grid',
        'Grid content'      => 'table.grid tbody',
        'Filters'           => 'div.filter-box',
        'Grid toolbar'      => 'div.grid-toolbar',
        'Manage filters'    => 'div.filter-list',
        'Configure columns' => 'a:contains("Columns")',
        'View selector'     => '#view-selector',
        'Views list'        => '.ui-multiselect-menu.highlight-hover',
        'Select2 results'   => '#select2-drop .select2-results',
        'Mass Edit'         => '.mass-actions-panel .action i.icon-edit',
    ];
    /**
     * Find a view in the list
     *
     * @param string $viewLabel
     *
     * @return NodeElement|null
     */
    public function findView($viewLabel)
    {
        $this
            ->find('css', $this->selectors['View selector'])
            ->getParent()
            ->find('css', 'button.pimmultiselect')
            ->click();

        return $this
            ->find('css', $this->selectors['Views list'])
            ->find('css', sprintf('label:contains("%s")', $viewLabel));
    }

    /**
     * Click on view in the view select
     *
     * @param string $viewLabel
     *
     * @throws \InvalidArgumentException
     */
    public function applyView($viewLabel)
    {
        $view = $this->spin(function () use ($viewLabel) {
            return $this->findView($viewLabel);
        }, sprintf('Impossible to find view "%s"', $viewLabel));

        $view->click();
    }

}