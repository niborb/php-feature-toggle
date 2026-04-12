<?php

declare(strict_types=1);

namespace Niborb\FeatureToggle\Entity;

interface FeatureInterface
{
    public function getName(): string;

    public function enable(): static;

    public function disable(): static;

    public function isEnabled(): bool;
}
