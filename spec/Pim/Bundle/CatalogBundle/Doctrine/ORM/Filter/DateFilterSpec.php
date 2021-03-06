<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class DateFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            ['pim_catalog_date'],
            ['created', 'updated'],
            ['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($qb);

        $qb->getRootAliases()->willReturn(['p']);
    }

    function it_is_a_date_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter');
    }

    function it_is_a_field_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY', 'NOT EMPTY', '!=']);

        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_date_fields()
    {
        $this->supportsField('created')->shouldReturn(true);
        $this->supportsField('updated')->shouldReturn(true);
        $this->supportsField('other')->shouldReturn(false);
    }

    function it_supports_date_attributes(AttributeInterface $dateAtt, AttributeInterface $otherAtt)
    {
        $dateAtt->getAttributeType()->willReturn('pim_catalog_date');
        $this->supportsAttribute($dateAtt)->shouldReturn(true);
        $otherAtt->getAttributeType()->willReturn('pim_catalog_other');
        $this->supportsAttribute($otherAtt)->shouldReturn(false);
    }

    function it_adds_a_less_than_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.updated_at < '2014-03-15'")->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->lt('p.updated_at', '2014-03-15')->willReturn("p.updated_at < '2014-03-15'")->shouldBeCalledTimes(2);
        $expr->literal('2014-03-15')->willReturn('2014-03-15')->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated_at', '<', '2014-03-15');
        $this->addFieldFilter('updated_at', '<', new \DateTime('2014-03-15'));
    }

    function it_adds_a_empty_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->expr()->willReturn($expr);
        $expr->isNull('p.updated_at')->willReturn('p.updated_at IS NULL');
        $qb->andWhere('p.updated_at IS NULL')->shouldBeCalled();

        $this->addFieldFilter('updated_at', 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->expr()->willReturn($expr);
        $expr->isNotNull('p.updated_at')->shouldBeCalled()->willReturn('p.updated_at IS NOT NULL');
        $qb->andWhere('p.updated_at IS NOT NULL')->shouldBeCalled();

        $this->addFieldFilter('updated_at', 'NOT EMPTY', null);
    }

    function it_adds_a_greater_than_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.updated_at > '2014-03-15 23:59:59'")->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->gt('p.updated_at', '2014-03-15 23:59:59')
            ->shouldBeCalled()
            ->willReturn("p.updated_at > '2014-03-15 23:59:59'")
            ->shouldBeCalledTimes(2);
        $expr->literal('2014-03-15 23:59:59')->willReturn('2014-03-15 23:59:59')->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated_at', '>', '2014-03-15');
        $this->addFieldFilter('updated_at', '>', new \DateTime('2014-03-15'));
    }

    function it_throws_an_exception_if_value_is_not_a_string_an_array_or_a_datetime()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected('updated_at', 'array with 2 elements, string or \DateTime', 'filter', 'date', print_r(123, true))
        )->during('addFieldFilter', ['updated_at', '>', 123]);
    }

    function it_throws_an_error_if_data_is_not_a_valid_date_format()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected('updated_at', 'a string with the format yyyy-mm-dd', 'filter', 'date', 'not a valid date format')
        )->during('addFieldFilter', ['updated_at', '>', ['not a valid date format', 'WRONG']]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_strings_or_dates()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated_at',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                123
            )
        )->during('addFieldFilter', ['updated_at', '>', [123, 123]]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_two_values()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated_at',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r([123, 123, 'three'], true)
            )
        )->during('addFieldFilter', ['updated_at', '>', [123, 123, 'three']]);
    }

    function it_adds_a_between_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb
            ->andWhere("p.updated_at > '2014-03-15' AND p.updated_at < '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn($qb);
        $expr
            ->andX("p.updated_at > '2014-03-15'", "p.updated_at < '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn("p.updated_at > '2014-03-15' AND p.updated_at < '2014-03-20 23:59:59'");
        $qb->expr()->willReturn($expr);

        $expr->gt('p.updated_at', '2014-03-15')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.updated_at > '2014-03-15'");
        $expr->lt('p.updated_at', '2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.updated_at < '2014-03-20 23:59:59'");
        $expr->literal('2014-03-15')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-15');
        $expr->literal('2014-03-20 23:59:59')->willReturn('2014-03-20 23:59:59')->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated_at', 'BETWEEN', ['2014-03-15', '2014-03-20']);
        $this->addFieldFilter('updated_at', 'BETWEEN', [new \DateTime('2014-03-15'), new \DateTime('2014-03-20')]);
    }

    function it_adds_an_equal_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.updated_at > '2014-03-20' AND p.updated_at < '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn($qb);
        $expr->andX("p.updated_at > '2014-03-20'", "p.updated_at < '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn("p.updated_at > '2014-03-20' AND p.updated_at < '2014-03-20 23:59:59'");
        $qb->expr()->willReturn($expr);

        $expr->gt('p.updated_at', '2014-03-20')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.updated_at > '2014-03-20'");
        $expr->lt('p.updated_at', '2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.updated_at < '2014-03-20 23:59:59'");
        $expr->literal('2014-03-20')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-20');
        $expr->literal('2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-20 23:59:59');

        $this->addFieldFilter('updated_at', '=', '2014-03-20');
        $this->addFieldFilter('updated_at', '=', new \DateTime('2014-03-20'));
    }

    function it_adds_a_not_equal_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.updated_at > '2014-03-20' AND p.updated_at < '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn($qb);
        $expr->orX("p.updated_at < '2014-03-20'", "p.updated_at > '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn("p.updated_at > '2014-03-20' AND p.updated_at < '2014-03-20 23:59:59'");
        $qb->expr()->willReturn($expr);

        $expr->lt('p.updated_at', '2014-03-20')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.updated_at < '2014-03-20'");
        $expr->gt('p.updated_at', '2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.updated_at > '2014-03-20 23:59:59'");
        $expr->literal('2014-03-20')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-20');
        $expr->literal('2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-20 23:59:59');

        $this->addFieldFilter('updated_at', '!=', '2014-03-20');
        $this->addFieldFilter('updated_at', '!=', new \DateTime('2014-03-20'));
    }

    function it_adds_a_not_between_filter_on_an_field_in_the_query(QueryBuilder $qb, Expr $expr)
    {
        $qb->andWhere("p.updated_at < '2014-03-15' OR p.updated_at > '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn($qb);
        $expr
            ->orX("p.updated_at < '2014-03-15'", "p.updated_at > '2014-03-20 23:59:59'")
            ->shouldBeCalledTimes(2)
            ->willReturn("p.updated_at < '2014-03-15' OR p.updated_at > '2014-03-20 23:59:59'");
        $qb->expr()->willReturn($expr);

        $expr->lt('p.updated_at', '2014-03-15')->shouldBeCalledTimes(2)->willReturn("p.updated_at < '2014-03-15'");
        $expr->gt('p.updated_at', '2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn("p.updated_at > '2014-03-20 23:59:59'");
        $expr->literal('2014-03-15')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-15');
        $expr->literal('2014-03-20 23:59:59')
            ->shouldBeCalledTimes(2)
            ->willReturn('2014-03-20 23:59:59');

        $this->addFieldFilter('updated_at', 'NOT BETWEEN', ['2014-03-15', '2014-03-20']);
        $this->addFieldFilter('updated_at', 'NOT BETWEEN', [new \DateTime('2014-03-15'), new \DateTime('2014-03-20')]);
    }

    function it_adds_an_empty_operator_filter_on_an_attribute_to_the_query(
        $attrValidatorHelper,
        AttributeInterface $attribute,
        QueryBuilder $qb,
        Expr $expr
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $qb->getRootAlias()->willReturn('p');
        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('code');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $qb->expr()->willReturn($expr);
        $qb->andWhere(null)->willReturn($expr);

        $qb->leftJoin(
            'p.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalled();

        $this->addAttributeFilter($attribute, 'EMPTY', null);
    }

    function it_adds_a_greater_than_filter_on_an_attribute_to_the_query(
        AttributeInterface $attribute,
        $attrValidatorHelper,
        QueryBuilder $qb,
        Expr $expr,
        Expr\Comparison $comparison
    ) {
        $attrValidatorHelper->validateLocale($attribute, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($attribute, Argument::any())->shouldBeCalled();

        $qb->getRootAlias()->willReturn('p');
        $attribute->getBackendType()->willReturn('backend_type');
        $attribute->getCode()->willReturn('code');
        $attribute->getId()->willReturn(42);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15')->willReturn('code');
        $expr->literal('en_US')->willReturn('code');
        $expr->literal('mobile')->willReturn('code');

        $expr->gt(Argument::any(), 'code')->willReturn($comparison)->shouldBeCalledTimes(2);
        $comparison->__toString()->willReturn();

        $qb->innerJoin(
            'p.values',
            Argument::any(),
            'WITH',
            Argument::any()
        )->shouldBeCalledTimes(2);

        $this->addAttributeFilter($attribute, '>', '2014-03-15');
        $this->addAttributeFilter($attribute, '>', new \DateTime('2014-03-15'));
    }
}
