<?php

namespace spec\Niborb\FeatureToggle\Cache;

use Doctrine\Common\Cache\Cache;
use Niborb\FeatureToggle\Cache\CacheProxy;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\ExpressionLanguage\ParsedExpression;

/**
 * Class ExpressionDoctrineCacheProxySpec
 * @package spec\Niborb\FeatureToggle\Cache
 *
 * @mixin CacheProxy
 */
class CacheProxySpec extends ObjectBehavior
{
    /**
     * @var Cache
     */
    private $cache;

    function let(Cache $cache)
    {
        $this->beConstructedWith($cache);

        $this->cache = $cache;
    }


    function it_is_initializable()
    {
        $this->shouldHaveType('Niborb\FeatureToggle\Cache\CacheProxy');
    }

    function it_is_an_expression_language_cache()
    {
        $this->shouldHaveType('Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface');
    }

    function it_should_save_cache_entry_in_doctrine_cache(ParsedExpression $parsedExpression)
    {
        $this->save('cache-key', $parsedExpression);

        $this->cache->save('cache-key', Argument::any())->shouldHaveBeenCalled();
    }

    function it_should_fetch_cache_entry_from_doctrine_cache(ParsedExpression $parsedExpression)
    {
        $this->cache->fetch('cache-key')->willReturn($parsedExpression)->shouldBeCalled();
        $this->fetch('cache-key')->shouldReturn($parsedExpression);
    }

    function it_should_return_null_if_no_cache_could_be_fetched(ParsedExpression $parsedExpression)
    {
        $this->cache->fetch('does-not-exists')->willReturn(false);
        $this->fetch('does-not-exists')->shouldReturn(null);
    }

}
