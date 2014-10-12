<?php

namespace spec\Niborb\FeatureToggle;

use Niborb\FeatureToggle\DataProvider\ArrayDataProvider;
use Niborb\FeatureToggle\Entity\Feature;
use Niborb\FeatureToggle\Toggle;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/***
 * Class ToggleSpec
 * @package spec\Niborb\FeatureToggle
 *
 * @mixin Toggle
 */
class ToggleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Niborb\FeatureToggle\Toggle');
    }

    function it_could_use_optionally_Symfony_ExpressionLanguage(ExpressionLanguage $expressionLanguage)
    {
        $this->setExpressionLanguage($expressionLanguage);
    }

    function it_could_use_a_dataprovider(
        ArrayDataProvider $arrayDataProvider
    )
    {
        $this->setDataProvider($arrayDataProvider);
    }

    function it_should_check_dataprovider_to_fetch_feature(
        ArrayDataProvider $arrayDataProvider
    )
    {
        $feature = new Feature('feature-name');

        $this->addFeature($feature);

        $this->setDataProvider($arrayDataProvider);

        $this->isEnabled('feature-name');

        $arrayDataProvider->fetchFeature('feature-name')->shouldHaveBeenCalled();
    }

    function it_can_add_features(Feature $feature)
    {
        $this->addFeature($feature);
        $this->hasFeature($feature)->shouldReturn(true);
    }

    function it_can_check_if_feature_is_enabled(Feature $feature)
    {
        $feature->getName()->willReturn('feature-name');
        $feature->isEnabled()->willReturn(true);
        $feature->getExpression()->willReturn('');

        $this->addFeature($feature);

        $this->isEnabled('feature-name')->shouldReturn(true);

        $feature->isEnabled()->willReturn(false);

        $this->isEnabled('feature-name')->shouldReturn(false);
    }

    function it_should_validate_feature_condition(
        Feature $feature,
        ExpressionLanguage $expressionLanguage
    ) {
        $feature->getName()->willReturn('feature-name');
        $feature->isEnabled()->willReturn(true);
        $feature->getExpression()->willReturn('2 > 3');

        $this->addFeature($feature);

        $expressionLanguage->evaluate('2 > 3', [])->willReturn(false);
        $this->setExpressionLanguage($expressionLanguage);

        $this->isEnabled('feature-name')->shouldReturn(false);

        $feature->getExpression()->willReturn('2 < 3');
        $expressionLanguage->evaluate('2 < 3', [])->willReturn(true);

        $this->isEnabled('feature-name')->shouldReturn(true);
    }

    function it_should_validate_feature_condition_with_context(
        Feature $feature,
        ExpressionLanguage $expressionLanguage
    ) {
        $feature->getName()->willReturn('feature-name');
        $feature->isEnabled()->willReturn(true);


        $this->addFeature($feature);
        $this->setExpressionLanguage($expressionLanguage);

        $feature->getExpression()->willReturn('n > 0');
        $expressionLanguage->evaluate('n > 0', ['n' => 1])->willReturn(true);

        $this->isEnabled('feature-name', ['n' => 1])->shouldReturn(true);

        $feature->getExpression()->willReturn('n < 0');
        $expressionLanguage->evaluate('n < 0', ['n' => 1])->willReturn(false);

        $this->isEnabled('feature-name', ['n' => 1])->shouldReturn(false);
    }

    function it_should_ignore_unknown_features_and_handles_them_as_disabled_features()
    {
        $this->isEnabled('non-existing-feature')->shouldReturn(false);
    }

    function it_should_ignore_expression_language_for_features_without_expressions(
        Feature $feature,
        ExpressionLanguage $expressionLanguage
    )
    {
        $feature->getName()->willReturn('feature-name');
        $feature->isEnabled()->willReturn(true);
        $feature->getExpression()->willReturn('');

        $this->addFeature($feature);

        $this->setExpressionLanguage($expressionLanguage);

        $this->isEnabled('feature-name')->shouldReturn(true);
    }

    function it_should_integrate_with_expression_language_with_context()
    {
        $expressionLanguage = new ExpressionLanguage();

        $this->setExpressionLanguage($expressionLanguage);

        $feature = new Feature('feature-name');
        $feature->disable();
        $feature->setExpression('n > 0');

        $this->addFeature($feature);

        $this->isEnabled('feature-name')->shouldReturn(false);
        $feature->enable();

        // exception should be thrown, since the expression requires
        // a context to know what 'n' in the expression n > 0 is
        $this->shouldThrow('\RuntimeException')->duringIsEnabled('feature-name');

        $this->isEnabled('feature-name', ['n' => 1])->shouldReturn(true);
        $this->isEnabled('feature-name', ['n' => -1])->shouldReturn(false);
        $feature->disable();
        $this->isEnabled('feature-name', ['n' => -1])->shouldReturn(false);
    }



}

