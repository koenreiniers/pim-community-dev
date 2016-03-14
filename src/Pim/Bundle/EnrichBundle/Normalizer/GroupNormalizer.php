<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Group normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $groupNormalizer;

    /** @var StructureVersionProviderInterface */
    protected $structureVersionProvider;

    /**
     * @param NormalizerInterface               $groupNormalizer
     * @param StructureVersionProviderInterface $structureVersionProvider
     */
    public function __construct(
        NormalizerInterface $groupNormalizer,
        StructureVersionProviderInterface $structureVersionProvider
    ) {
        $this->groupNormalizer          = $groupNormalizer;
        $this->structureVersionProvider = $structureVersionProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($group, $format = null, array $context = [])
    {
        $normalizedGroup = $this->groupNormalizer->normalize($group, 'json', $context);

        $normalizedGroup['meta'] = [
            'id'                => $group->getId(),
            'structure_version' => $this->structureVersionProvider->getStructureVersion()
        ];

        return $normalizedGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupInterface && in_array($format, $this->supportedFormats);
    }
}
