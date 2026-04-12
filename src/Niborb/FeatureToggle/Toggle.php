<?php

declare(strict_types=1);

namespace Niborb\FeatureToggle;

use Niborb\FeatureToggle\DataProvider\ArrayDataProvider;
use Niborb\FeatureToggle\Entity\FeatureExpressionInterface;
use Niborb\FeatureToggle\Entity\FeatureInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class Toggle
{
    private DataProvider $dataProvider;

    public function __construct(
        private readonly ?ExpressionLanguage $expressionLanguage = null,
        ?DataProvider $dataProvider = null,
    ) {
        $this->dataProvider = $dataProvider ?? new ArrayDataProvider();
    }

    public function addFeature(FeatureInterface $feature): self
    {
        $this->dataProvider->addFeature($feature);

        return $this;
    }

    public function hasFeature(FeatureInterface $feature): bool
    {
        return $this->dataProvider->fetchFeature($feature->getName()) !== null;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws \RuntimeException if the feature expression could not be evaluated
     */
    public function isEnabled(string $featureName, array $context = []): bool
    {
        $feature = $this->dataProvider->fetchFeature($featureName);
        if ($feature === null || !$feature->isEnabled()) {
            return false;
        }

        if ($feature instanceof FeatureExpressionInterface) {
            return $this->validate($feature, $context);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function validate(FeatureExpressionInterface $feature, array $context): bool
    {
        $expression = $feature->getExpression();
        if ($expression === '' || $this->expressionLanguage === null) {
            return true;
        }

        try {
            return (bool) $this->expressionLanguage->evaluate($expression, $context);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Could not evaluate expression ' . $expression, 0, $e);
        }
    }
}
