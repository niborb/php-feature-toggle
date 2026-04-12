<?php

declare(strict_types=1);

namespace Niborb\FeatureToggle\Tests\DataProvider;

use Niborb\FeatureToggle\DataProvider;
use Niborb\FeatureToggle\DataProvider\ArrayDataProvider;
use Niborb\FeatureToggle\Entity\Feature;
use Niborb\FeatureToggle\Entity\FeatureInterface;
use PHPUnit\Framework\TestCase;

final class ArrayDataProviderTest extends TestCase
{
    public function testItImplementsDataProviderAndIteratorAggregate(): void
    {
        $provider = new ArrayDataProvider();

        self::assertInstanceOf(DataProvider::class, $provider);
        self::assertInstanceOf(\IteratorAggregate::class, $provider);
    }

    public function testItCanBeConstructedWithAnArrayOfFeatures(): void
    {
        $featureOne = new Feature('feature-one');
        $featureTwo = new Feature('feature-two');

        $provider = new ArrayDataProvider([$featureOne, $featureTwo]);

        self::assertSame($featureOne, $provider->fetchFeature('feature-one'));
        self::assertSame($featureTwo, $provider->fetchFeature('feature-two'));
    }

    public function testItThrowsLogicExceptionIfANonFeatureIsAdded(): void
    {
        $this->expectException(\LogicException::class);

        new ArrayDataProvider([new \stdClass()]);
    }

    public function testFetchFeatureReturnsNullForUnknownFeature(): void
    {
        self::assertNull((new ArrayDataProvider())->fetchFeature('unknown'));
    }

    public function testItCanFetchAFeatureByName(): void
    {
        $feature = new Feature('feature-name');
        $provider = new ArrayDataProvider();
        $provider->addFeature($feature);

        self::assertSame($feature, $provider->fetchFeature('feature-name'));
    }

    public function testIteratorReturnsAllFeatures(): void
    {
        $featureOne = new Feature('feature-one');
        $featureTwo = new Feature('feature-two');

        $provider = new ArrayDataProvider([$featureOne, $featureTwo]);

        $iterator = $provider->getIterator();
        self::assertInstanceOf(\ArrayIterator::class, $iterator);
        self::assertSame(
            ['feature-one' => $featureOne, 'feature-two' => $featureTwo],
            iterator_to_array($iterator),
        );
    }
}
