<?php

namespace Pim\Component\Catalog\Validator\ConstraintGuesser;

use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
use Pim\Component\Catalog\Validator\Constraints\UniqueValue;

/**
 * Guesser
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        $availableTypes = [
            AbstractAttributeType::BACKEND_TYPE_VARCHAR,
            AbstractAttributeType::BACKEND_TYPE_DATE,
            AbstractAttributeType::BACKEND_TYPE_DATETIME,
            AbstractAttributeType::BACKEND_TYPE_DECIMAL,
            AbstractAttributeType::BACKEND_TYPE_INTEGER
        ];

        return in_array($attribute->getBackendType(), $availableTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        $constraints = [];

        if ($attribute->isUnique()) {
            $constraints[] = new UniqueValue();
        }

        return $constraints;
    }
}
