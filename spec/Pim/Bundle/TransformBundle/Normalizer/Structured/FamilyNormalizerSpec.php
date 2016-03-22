<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Bundle\TransformBundle\Normalizer\Flat\TranslationNormalizer;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface;
use Prophecy\Argument;

class FamilyNormalizerSpec extends ObjectBehavior
{
    function let(
        TranslationNormalizer $normalizer,
        CollectionFilterInterface $filter,
        AttributeRepositoryInterface $attributeRepository,
        AttributeRequirementRepositoryInterface $requirementsRepository
    ) {
        $this->beConstructedWith($normalizer, $filter, $attributeRepository, $requirementsRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Normalizer\Structured\FamilyNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_family_normalization_into_json_and_xml(FamilyInterface $family)
    {
        $this->supportsNormalization($family, 'csv')->shouldBe(false);
        $this->supportsNormalization($family, 'json')->shouldBe(true);
        $this->supportsNormalization($family, 'xml')->shouldBe(true);
    }

    function it_normalizes_family(
        $normalizer,
        $attributeRepository,
        $requirementsRepository,
        $filter,
        FamilyInterface $family,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $price
    ) {
        $attributeRepository->getAttributesByFamily($family)->willReturn([$name, $description, $price]);
        $requirementsRepository->getRequiredAttributesCodesByFamily($family)->willReturn([
            ['attribute' => 'name', 'channel' => 'ecommerce'],
        ]);

        $filter->filterCollection([$name, $description, $price], 'pim.internal_api.attribute.view')
            ->willReturn([$name, $price]);

        $normalizer->normalize(Argument::cetera())->willReturn([]);
        $family->getCode()->willReturn('mugs');
        $family->getAttributeAsLabel()->willReturn($name);
        $name->getCode()->willReturn('name');
        $price->getCode()->willReturn('price');

        $this->normalize($family)->shouldReturn(
            [
                'code'                   => 'mugs',
                'attributes'             => ['name', 'price'],
                'attribute_as_label'     => 'name',
                'requirements-ecommerce' => ['name'],
            ]
        );
    }
}
