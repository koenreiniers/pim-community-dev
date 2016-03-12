<?php

namespace Akeneo\Component\Batch\Item;

/**
 * Named step element
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface NamedStepElementInterface
{
    /**
     * Return name
     *
     * @return string
     */
    public function getName();
}
