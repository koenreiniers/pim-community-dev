<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Family normalizer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['json', 'xml'];

    /** @var TranslationNormalizer */
    protected $transNormalizer;

    /** @var CollectionFilterInterface */
    protected $collectionFilter;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param TranslationNormalizer                   $transNormalizer
     * @param AttributeRepositoryInterface            $attributeRepository
     * @param AttributeRequirementRepositoryInterface $requirementsRepository
     * @param CollectionFilterInterface|null          $collectionFilter
     */
    public function __construct(
        TranslationNormalizer $transNormalizer,
        CollectionFilterInterface $collectionFilter,
        AttributeRepositoryInterface $attributeRepository,
        AttributeRequirementRepositoryInterface $requirementsRepository
    ) {
        $this->transNormalizer = $transNormalizer;
        $this->collectionFilter = $collectionFilter;
        $this->attributeRepository = $attributeRepository;
        $this->requirementsRepository = $requirementsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedRequirements = $this->normalizeRequirements($object);
        $transNormalized = $this->transNormalizer->normalize($object, $format, $context);

        $defaults = ['code' => $object->getCode()];

        $normalizedData = [
            'attributes'         => $this->normalizeAttributes($object),
            'attribute_as_label' => ($object->getAttributeAsLabel()) ? $object->getAttributeAsLabel()->getCode() : '',
        ];

        return array_merge($defaults, $transNormalized, $normalizedData, $normalizedRequirements);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FamilyInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the attributes
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    protected function normalizeAttributes(FamilyInterface $family)
    {
        $attributes = $this->attributeRepository->getAttributesByFamily($family);

        if (null !== $this->collectionFilter) {
            $attributes = $this->collectionFilter->filterCollection($attributes, 'pim.internal_api.attribute.view');
        }

        $normalizedAttributes = [];
        foreach ($attributes as $attribute) {
            $normalizedAttributes[] = $attribute->getCode();
        }

        return $normalizedAttributes;
    }

    /**
     * Normalize the requirements
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    protected function normalizeRequirements(FamilyInterface $family)
    {
        $requirements = $this->requirementsRepository->getRequiredAttributesCodesByFamily($family);
        $required = [];

        foreach ($requirements as $requirement) {
            $key = sprintf('requirements-%s', $requirement['channel']);

            if (!isset($required[$key])) {
                $required[$key] = [];
            }

            $required[$key][] = $requirement['attribute'];
        }

        return $required;
    }
}
