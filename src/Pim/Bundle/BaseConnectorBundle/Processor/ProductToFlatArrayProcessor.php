<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Process a product to an array
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToFlatArrayProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /** @var Serializer */
    protected $serializer;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @Channel
     *
     * @var string Channel code
     */
    protected $channel;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var array Normalizer context */
    protected $normalizerContext;

    /** @var array */
    protected $mediaAttributeTypes;

    /** @var string */
    protected $decimalSeparator = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;

    /** @var array */
    protected $decimalSeparators;

    /** @var string */
    protected $dateFormat = LocalizerInterface::DEFAULT_DATE_FORMAT;

    /** @var array */
    protected $dateFormats;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /**
     * @param Serializer                 $serializer
     * @param ChannelRepositoryInterface $channelRepository
     * @param ProductBuilderInterface    $productBuilder
     * @param string[]                   $mediaAttributeTypes
     * @param array                      $decimalSeparators
     * @param array                      $dateFormats
     */
    public function __construct(
        Serializer $serializer,
        ChannelRepositoryInterface $channelRepository,
        ProductBuilderInterface $productBuilder,
        array $mediaAttributeTypes,
        array $decimalSeparators,
        array $dateFormats
    ) {
        $this->serializer          = $serializer;
        $this->channelRepository   = $channelRepository;
        $this->mediaAttributeTypes = $mediaAttributeTypes;
        $this->decimalSeparators   = $decimalSeparators;
        $this->dateFormats         = $dateFormats;
        $this->productBuilder      = $productBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $contextChannel = $this->channelRepository->findOneByIdentifier($this->channel);
        $this->productBuilder->addMissingProductValues(
            $product,
            [$contextChannel],
            $contextChannel->getLocales()->toArray()
        );

        $data['media'] = [];
        $mediaValues   = $this->getMediaProductValues($product);

        foreach ($mediaValues as $mediaValue) {
            $data['media'][] = $this->serializer->normalize(
                $mediaValue->getMedia(),
                'flat',
                ['field_name' => 'media', 'prepare_copy' => true, 'value' => $mediaValue]
            );
        }

        $data['product'] = $this->serializer->normalize($product, 'flat', $this->getNormalizerContext());

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->channelRepository->getLabelsIndexedByCode(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                ]
            ],
            'decimalSeparator' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->decimalSeparators,
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.decimalSeparator.label',
                    'help'     => 'pim_base_connector.export.decimalSeparator.help'
                ]
            ],
            'dateFormat' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->dateFormats,
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.dateFormat.label',
                    'help'     => 'pim_base_connector.export.dateFormat.help',
                ]
            ],
        ];
    }

    /**
     * Set channel
     *
     * @param string $channelCode Channel code
     *
     * @return $this
     */
    public function setChannel($channelCode)
    {
        $this->channel = $channelCode;

        return $this;
    }

    /**
     * Get channel
     *
     * @return string Channel code
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set the separator for decimal
     *
     * @param string $decimalSeparator
     */
    public function setDecimalSeparator($decimalSeparator)
    {
        $this->decimalSeparator = $decimalSeparator;
    }

    /**
     * Get the delimiter for decimal
     *
     * @return string
     */
    public function getDecimalSeparator()
    {
        return $this->decimalSeparator;
    }

    /**
     * Set the date format
     *
     * @param string $dateFormat
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * Get the date format
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Get normalizer context
     *
     * @return array $normalizerContext
     */
    protected function getNormalizerContext()
    {
        if (null === $this->normalizerContext) {
            $this->normalizerContext = [
                'scopeCode'         => $this->channel,
                'localeCodes'       => $this->getLocaleCodes($this->channel),
                'decimal_separator' => $this->decimalSeparator,
                'date_format'       => $this->dateFormat,
            ];
        }

        return $this->normalizerContext;
    }

    /**
     * Get locale codes for a channel
     *
     * @param string $channelCode
     *
     * @return array
     */
    protected function getLocaleCodes($channelCode)
    {
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);

        return $channel->getLocaleCodes();
    }

    /**
     * Fetch medias product values
     *
     * @param ProductInterface $product
     *
     * @return ProductValueInterface[]
     */
    protected function getMediaProductValues(ProductInterface $product)
    {
        $values = [];
        foreach ($product->getValues() as $value) {
            if (in_array(
                $value->getAttribute()->getAttributeType(),
                $this->mediaAttributeTypes
            )) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
