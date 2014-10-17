<?php

namespace Niborb\FeatureToggle\Entity;


/**
 * Interface FeatureInterface
 * @package Niborb\FeatureToggle\Entity
 */
interface FeatureExpressionInterface extends FeatureInterface
{
    /**
     * @param $expression
     * @return FeatureExpressionInterface
     */
    public function setExpression($expression);

    /**
     * @return string
     */
    public function getExpression();

}