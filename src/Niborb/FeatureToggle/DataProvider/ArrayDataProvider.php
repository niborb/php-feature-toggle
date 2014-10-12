<?php

namespace Niborb\FeatureToggle\DataProvider;

use Niborb\FeatureToggle\DataProvider;
use Niborb\FeatureToggle\Entity\Feature;
use Traversable;

class ArrayDataProvider implements DataProvider, \IteratorAggregate
{

    /**
     * @var Feature[]
     */
    private $features = [];

    /**
     * @param Feature[] $features
     */
    public function __construct(array $features = [])
    {
        $this->addFeatures($features);
    }

    /**
     * @param string $featureName
     * @return null|Feature null if feature cannot be fetched
     */
    public function fetchFeature($featureName)
    {
        if (array_key_exists($featureName, $this->features)) {
            return $this->features[$featureName];
        }

        return null;
    }

    /**
     * @param Feature $feature
     * @return $this
     */
    public function addFeature(Feature $feature)
    {
        $this->features[$feature->getName()] = $feature;

        return $this;
    }

    /**
     * @param Feature[] $features
     */
    public function addFeatures(array $features)
    {
        array_walk($features, function (&$value, $key) {
            if (!$value instanceof Feature) {
                throw new \LogicException('Item ' . $key . ' is not a Feature');
            }

            $this->addFeature($value);
        });
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->features);
    }

}
