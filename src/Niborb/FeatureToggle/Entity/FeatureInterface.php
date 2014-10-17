<?php

namespace Niborb\FeatureToggle\Entity;


/**
 * Interface FeatureInterface
 * @package Niborb\FeatureToggle\Entity
 */
interface FeatureInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return FeatureInterface
     */
    public function enable();

    /**
     * @return boolean
     */
    public function isEnabled();

    /**
     * @return FeatureInterface
     */
    public function disable();

}