<?php

declare(strict_types=1);

namespace Niborb\FeatureToggle\Entity;

interface FeatureExpressionInterface extends FeatureInterface
{
    public function setExpression(string $expression): static;

    public function getExpression(): string;
}
