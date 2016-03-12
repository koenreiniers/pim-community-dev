<?php

namespace Akeneo\Component\Batch\Step;

/**
 * Interface ConfigurableInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface ConfigurableInterface
{
    /**
     * Provide a configuration
     *
     * @return array
     */
    public function getConfiguration();

    /**
     * Set a configuration
     *
     * @param array $config
     */
    public function setConfiguration(array $config);
}
