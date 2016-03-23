<?php

namespace Pim\Behat\Decorator\ViewDecorator;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to manipulate page views
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViewSelectorDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Find a view in the list
     *
     * @param string $viewLabel
     *
     * @return NodeElement|null
     */
    public function showViewList($viewLabel)
    {
        $this->find('css', 'button.pimmultiselect')->click();
    }

   /**
     * Clicks creates the view button
     */
    public function createView()
    {
        $this->find('css', '#create-view')->click();
    }

    /**
     * Update view button
     */
    public function updateView()
    {
        $this->find('css', '#update-view')->click();
    }

    /**
     * Delete the view button
     */
    public function deleteView()
    {
        $this->find('css', '#remove-view')->click();
    }
}