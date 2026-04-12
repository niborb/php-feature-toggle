<?php

declare(strict_types=1);

namespace Niborb\FeatureToggle;

use Niborb\FeatureToggle\Entity\FeatureInterface;

interface DataProvider
{
    public function fetchFeature(string $featureName): ?FeatureInterface;

    public function addFeature(FeatureInterface $feature): static;
}
