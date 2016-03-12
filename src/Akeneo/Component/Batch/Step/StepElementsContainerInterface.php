<?php

namespace Akeneo\Component\Batch\Step;

/**
 * Defines if a a step contains step elements
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface StepElementsContainerInterface
{
    /**
     * Get the configurable step elements
     *
     * TODO: replace by getStepElements() and keep other method as deprecated
     * TODO: only used by FormType, the system should be replaced by a registry
     *
     * @return array
     */
    public function getConfigurableStepElements();
}
