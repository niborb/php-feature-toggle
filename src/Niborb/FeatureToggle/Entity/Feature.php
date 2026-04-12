<?php

declare(strict_types=1);

namespace Niborb\FeatureToggle\Entity;

class Feature implements FeatureExpressionInterface
{
    private readonly string $name;

    private bool $enabled = false;

    private string $expression = '';

    public function __construct(string $name)
    {
        if (trim($name) === '') {
            throw new \LogicException('Name cannot be empty');
        }

        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function enable(): static
    {
        $this->enabled = true;

        return $this;
    }

    public function disable(): static
    {
        $this->enabled = false;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setExpression(string $expression): static
    {
        $this->expression = $expression;

        return $this;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }
}
