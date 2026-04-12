<?php

declare(strict_types=1);

namespace Niborb\FeatureToggle\Tests\Entity;

use Niborb\FeatureToggle\Entity\Feature;
use Niborb\FeatureToggle\Entity\FeatureExpressionInterface;
use Niborb\FeatureToggle\Entity\FeatureInterface;
use PHPUnit\Framework\TestCase;

final class FeatureTest extends TestCase
{
    public function testImplementsFeatureInterfaces(): void
    {
        $feature = new Feature('feature-name');

        self::assertInstanceOf(FeatureInterface::class, $feature);
        self::assertInstanceOf(FeatureExpressionInterface::class, $feature);
    }

    public function testItHasAName(): void
    {
        self::assertSame('feature-name', (new Feature('feature-name'))->getName());
    }

    public function testItIsDisabledByDefault(): void
    {
        self::assertFalse((new Feature('feature-name'))->isEnabled());
    }

    public function testItCanBeEnabled(): void
    {
        $feature = new Feature('feature-name');
        $feature->enable();

        self::assertTrue($feature->isEnabled());
    }

    public function testItCanBeDisabledAfterEnabling(): void
    {
        $feature = new Feature('feature-name');

        self::assertFalse($feature->enable()->disable()->isEnabled());
    }

    public function testItCanContainAnExpression(): void
    {
        $feature = new Feature('feature-name');
        $feature->setExpression('2 < 3');

        self::assertSame('2 < 3', $feature->getExpression());
    }

    public function testItThrowsAnExceptionIfConstructedWithEmptyName(): void
    {
        $this->expectException(\LogicException::class);

        new Feature('');
    }

    public function testItThrowsAnExceptionIfConstructedWithWhitespaceName(): void
    {
        $this->expectException(\LogicException::class);

        new Feature('   ');
    }
}
