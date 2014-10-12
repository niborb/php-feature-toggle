<?php

namespace spec\Niborb\FeatureToggle\DataProvider;

use Niborb\FeatureToggle\DataProvider\ArrayDataProvider;
use Niborb\FeatureToggle\Entity\Feature;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class ArrayDataProviderSpec
 * @package spec\Niborb\FeatureToggle\DataProvider
 *
 * @mixin ArrayDataProvider
 */
class ArrayDataProviderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Niborb\FeatureToggle\DataProvider\ArrayDataProvider');
        $this->shouldHaveType('Niborb\FeatureToggle\DataProvider');
        $this->shouldHaveType('\IteratorAggregate');
    }

    function it_could_be_constructed_with_an_array_of_features(
        Feature $feature, Feature $otherFeature
    )
    {
        $features = [$feature, $otherFeature];

        $this->beConstructedWith($features);
    }

    function it_should_throw_LogicException_if_a_non_feature_class_is_added()
    {
        $feature = new \stdClass();

        $this->shouldThrow('\Exception')->during('__construct', [$feature]);
        $this->shouldThrow('\LogicException')->during('__construct', [[$feature]]);
    }


    function it_should_be_able_to_fetch_feature_by_name(
        Feature $feature
    )
    {
        $this->beConstructedWith([$feature]);

        $feature->getName()->willReturn('feature-name');


        $this->fetchFeature('feature-name')->shouldReturn($feature);
        $this->fetchFeature('non-existing-feature')->shouldReturn(null);
    }

    function it_should_be_able_to_fetch_features_passed_by_constructor(
        Feature $featureOne, Feature $featureTwo
    )
    {
        $featureOne->getName()->willReturn('feature-one')->shouldBeCalled();
        $featureTwo->getName()->willReturn('feature-two')->shouldBeCalled();

        $this->beConstructedWith([$featureOne, $featureTwo]);

        $this->fetchFeature('feature-one')->shouldReturn($featureOne);
        $this->fetchFeature('feature-two')->shouldReturn($featureTwo);
    }

    function it_should_have_an_array_iterator()
    {
        $this->getIterator()->shouldHaveType('\ArrayIterator');
    }

}
