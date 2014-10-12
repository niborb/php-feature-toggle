<?php

namespace Niborb\FeatureToggle\Cache;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;

class CacheProxy implements ParserCacheInterface
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Saves an expression in the cache.
     *
     * @param string $key The cache key
     * @param ParsedExpression $expression A ParsedExpression instance to store in the cache
     */
    public function save($key, ParsedExpression $expression)
    {
        $this->cache->save($key, $expression);
    }

    /**
     * Fetches an expression from the cache.
     *
     * @param string $key The cache key
     *
     * @return ParsedExpression|null
     */
    public function fetch($key)
    {
        $cacheResult = $this->cache->fetch($key);

        return $cacheResult !== false ? $cacheResult : null;
    }
}
