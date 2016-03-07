<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Date filter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    const DATETIME_FORMAT = 'Y-m-d';

    /** @var array */
    protected $supportedAttributes;

    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedAttributes
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $scope = null,
        $options = []
    ) {
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'date');

        if (Operators::IS_EMPTY === $operator) {
            $value = null;
        } else {
            $value = $this->formatValues($attribute->getCode(), $value);
        }

        $joinAlias    = $this->getUniqueAlias('filter' . $attribute->getCode());
        $backendField = sprintf('%s.%s', $joinAlias, $attribute->getBackendType());

        if ($operator === Operators::IS_EMPTY) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias() . '.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            );
            $this->qb->andWhere($this->prepareCriteriaCondition($backendField, $operator, $value));
        } elseif ($operator === Operators::NOT_BETWEEN) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias() . '.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            );
            $this->qb->andWhere(
                $this->qb->expr()->orX(
                    $this->qb->expr()->lt($backendField, $this->qb->expr()->literal($value[0])),
                    $this->qb->expr()->gt($backendField, $this->qb->expr()->literal($value[1]))
                )
            );
        } else {
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
            $condition .= ' AND ' . $this->prepareCriteriaCondition($backendField, $operator, $value);
            $this->qb->innerJoin(
                $this->qb->getRootAlias() . '.values',
                $joinAlias,
                'WITH',
                $condition
            );
        }

        return $this;
    }

    /**
     * Format values to string or array of strings
     *
     * @param string $type
     * @param mixed  $value
     *
     * @return mixed $value
     */
    protected function formatValues($type, $value)
    {
        if (is_array($value) && 2 !== count($value)) {
            throw InvalidArgumentException::expected(
                $type,
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r($value, true)
            );
        }

        if (is_array($value)) {
            $tmpValues = [];
            foreach ($value as $tmp) {
                $tmpValues[] = $this->formatSingleValue($type, $tmp);
            }
            $value = $tmpValues;
        } else {
            $value = $this->formatSingleValue($type, $value);
        }

        return $value;
    }

    /**
     * @param string $type
     * @param mixed  $value
     *
     * @return string
     */
    protected function formatSingleValue($type, $value)
    {
        if ($value instanceof \DateTime) {
            $value = $value->format(static::DATETIME_FORMAT);
        } elseif (is_string($value)) {
            $this->validateDateFormat($type, $value);
        } elseif (null !== $value) {
            throw InvalidArgumentException::expected(
                $type,
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r($value, true)
            );
        }

        return $value;
    }

    /**
     * Check if the date format is valid
     *
     * @param string $type
     * @param string $value
     */
    protected function validateDateFormat($type, $value)
    {
        $dateTime = \DateTime::createFromFormat(static::DATETIME_FORMAT, $value);

        if (!$dateTime || 0 < $dateTime->getLastErrors()['warning_count']) {
            throw InvalidArgumentException::expected(
                $type,
                'a string with the format ' . static::DATETIME_FORMAT,
                'filter',
                'date',
                $value
            );
        }
    }
}
