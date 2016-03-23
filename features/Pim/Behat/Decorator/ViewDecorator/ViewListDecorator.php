<?php

namespace Pim\Behat\Decorator\ViewDecorator;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to manipulate the list of views for the page
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViewListDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    public function findView($viewLabel)
    {
        return $this->spin(function () use ($viewLabel) {
            return $this
                ->find('css', sprintf('label:contains("%s")', $viewLabel));
        }, sprintf('Impossible to find view "%s"', $viewLabel));
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
        $this->findView($viewLabel)->click();
    }
}
