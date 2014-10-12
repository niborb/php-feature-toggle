<?php

namespace spec\Niborb\FeatureToggle\Entity;

use Niborb\FeatureToggle\Entity\Feature;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class FeatureSpec
 * @package spec\Niborb\FeatureToggle\Entity
 *
 * @mixin Feature
 */
class FeatureSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('feature-name');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Niborb\FeatureToggle\Entity\Feature');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldBeString();
    }

    function it_can_be_enabled()
    {
        $this->enable();

        $this->isEnabled()->shouldReturn(true);
    }

    function it_is_disabled_by_default()
    {
        $this->isEnabled()->shouldReturn(false);
    }

    function it_can_be_disabled_after_enabling()
    {
        $this
            ->enable()
            ->disable()
            ->isEnabled()->shouldReturn(false);
    }

    function it_requires_a_name()
    {
        $this->getName()->shouldnotBeEmpty();
    }

    function it_can_contain_an_expression()
    {
        $this->setExpression('2 < 3');
        $this->getExpression()->shouldReturn('2 < 3');
    }

    function it_should_throw_an_exception_if_constructed_with_empty_name()
    {
        $e = null;
        try {
            $instance = new Feature('');
        } catch (\Exception $e) {

        } finally {
            if (!$e instanceof \Exception) {
                throw new \Exception('it should throw ' . 'an exception if constructed {
                    with an empty name');
            }
        }
    }


    public function getMatchers()
    {
        return [
            'notBeEmpty' => function ($subject) {
                return is_string($subject) && strlen($subject) > 0;
            }
        ];
    }
}

