<?php

declare(strict_types=1);

namespace Niborb\FeatureToggle\DataProvider;

use Niborb\FeatureToggle\DataProvider;
use Niborb\FeatureToggle\Entity\FeatureInterface;

/**
 * @implements \IteratorAggregate<string, FeatureInterface>
 */
class ArrayDataProvider implements DataProvider, \IteratorAggregate
{
    /**
     * @var array<string, FeatureInterface>
     */
    private array $features = [];

    /**
     * @param iterable<mixed> $features
     */
    public function __construct(iterable $features = [])
    {
        $this->addFeatures($features);
    }

    public function fetchFeature(string $featureName): ?FeatureInterface
    {
        return $this->features[$featureName] ?? null;
    }

    public function addFeature(FeatureInterface $feature): self
    {
        $this->features[$feature->getName()] = $feature;

        return $this;
    }

    /**
     * @param iterable<mixed> $features
     */
    public function addFeatures(iterable $features): self
    {
        foreach ($features as $key => $feature) {
            if (!$feature instanceof FeatureInterface) {
                throw new \LogicException('Item ' . $key . ' is not a Feature');
            }

            $this->addFeature($feature);
        }

        return $this;
    }

    /**
     * @return \Traversable<string, FeatureInterface>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->features);
    }
}
