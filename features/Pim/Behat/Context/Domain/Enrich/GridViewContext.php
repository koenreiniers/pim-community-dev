<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * A context for managing the grid pagination and size
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GridViewContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param string $viewLabel
     *
     * @When /^I apply the "([^"]*)" view$/
     */
    public function iApplyTheView($viewLabel)
    {
        $this->datagrid->applyView($viewLabel);
        $this->wait();
    }

    /**
     * @When /^I delete the view$/
     */
    public function iDeleteTheView()
    {
        $this->getCurrentPage()->find('css', '#remove-view')->click();
        $this->wait();
    }

    /**
     * @param TableNode $table
     *
     * @return Then[]
     *
     * @When /^I create the view:$/
     */
    public function iCreateTheView(TableNode $table)
    {
        $this->getCurrentPage()->find('css', '#create-view')->click();

        return [
            new Step\Then('I fill in the following information in the popin:', $table),
            new Step\Then('I press the "OK" button')
        ];
    }

    /**
     * @When /^I update the view$/
     */
    public function iUpdateTheView()
    {
        $this->getCurrentPage()->find('css', '#update-view')->click();
        $this->wait();
    }

    /**
     * @param string $not
     * @param string $viewLabel
     *
     * @Then /^I should( not)? see the "([^"]*)" view$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheView($not, $viewLabel)
    {
        $view = $this->datagrid->findView($viewLabel);

        if (('' !== $not && null !== $view) || ('' === $not && null === $view)) {
            throw $this->createExpectationException(
                sprintf(
                    'View "%s" should%s be available.',
                    $viewLabel,
                    $not
                )
            );
        }
    }

}