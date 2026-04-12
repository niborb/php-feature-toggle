<?php

declare(strict_types=1);

namespace Niborb\FeatureToggle\Tests;

use Niborb\FeatureToggle\DataProvider;
use Niborb\FeatureToggle\Entity\Feature;
use Niborb\FeatureToggle\Toggle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ToggleTest extends TestCase
{
    public function testItCanAddAndDetectFeatures(): void
    {
        $toggle = new Toggle();
        $feature = new Feature('feature-name');

        $toggle->addFeature($feature);

        self::assertTrue($toggle->hasFeature($feature));
    }

    public function testItChecksIfFeatureIsEnabled(): void
    {
        $toggle = new Toggle();
        $feature = (new Feature('feature-name'))->enable();
        $toggle->addFeature($feature);

        self::assertTrue($toggle->isEnabled('feature-name'));

        $feature->disable();
        self::assertFalse($toggle->isEnabled('feature-name'));
    }

    public function testItIgnoresUnknownFeaturesAndHandlesThemAsDisabled(): void
    {
        self::assertFalse((new Toggle())->isEnabled('non-existing-feature'));
    }

    public function testItDelegatesFetchToInjectedDataProvider(): void
    {
        $provider = $this->createMock(DataProvider::class);
        $provider->expects(self::once())
            ->method('fetchFeature')
            ->with('feature-name')
            ->willReturn(null);

        $toggle = new Toggle(null, $provider);
        $toggle->isEnabled('feature-name');
    }

    public function testItValidatesFeatureExpression(): void
    {
        $expressionLanguage = $this->createMock(ExpressionLanguage::class);
        $expressionLanguage->expects(self::once())
            ->method('evaluate')
            ->with('2 > 3', [])
            ->willReturn(false);

        $toggle = new Toggle($expressionLanguage);
        $feature = (new Feature('feature-name'))->enable()->setExpression('2 > 3');
        $toggle->addFeature($feature);

        self::assertFalse($toggle->isEnabled('feature-name'));
    }

    public function testItValidatesFeatureExpressionWithContext(): void
    {
        $toggle = new Toggle(new ExpressionLanguage());
        $feature = (new Feature('feature-name'))->enable()->setExpression('n > 0');
        $toggle->addFeature($feature);

        self::assertTrue($toggle->isEnabled('feature-name', ['n' => 1]));
        self::assertFalse($toggle->isEnabled('feature-name', ['n' => -1]));
    }

    public function testItIgnoresExpressionLanguageForFeaturesWithoutExpression(): void
    {
        $expressionLanguage = $this->createMock(ExpressionLanguage::class);
        $expressionLanguage->expects(self::never())->method('evaluate');

        $toggle = new Toggle($expressionLanguage);
        $toggle->addFeature((new Feature('feature-name'))->enable());

        self::assertTrue($toggle->isEnabled('feature-name'));
    }

    public function testItWrapsEvaluationExceptionsAsRuntimeException(): void
    {
        $toggle = new Toggle(new ExpressionLanguage());
        // expression references a variable that is not in the context
        $toggle->addFeature((new Feature('feature-name'))->enable()->setExpression('n > 0'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not evaluate expression n > 0');

        $toggle->isEnabled('feature-name');
    }

    public function testItReturnsTrueWhenFeatureHasExpressionButNoExpressionLanguage(): void
    {
        $toggle = new Toggle();
        $toggle->addFeature((new Feature('feature-name'))->enable()->setExpression('n > 0'));

        // No ExpressionLanguage set: validation is skipped, feature treated as enabled.
        self::assertTrue($toggle->isEnabled('feature-name'));
    }
}
