<?php

namespace Niborb\FeatureToggle\Entity;

class Feature
{

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @var string
     */
    private $expression = '';


    /**
     * @param $name
     */
    public function __construct($name)
    {
        if (0 === strlen(trim($name))) {
            throw new \LogicException("Name cannot be empty");
        }

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return $this
     */
    public function disable()
    {
        $this->enabled = false;

        return $this;
    }


    /**
     * @param $expression
     * @return $this
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }
}
