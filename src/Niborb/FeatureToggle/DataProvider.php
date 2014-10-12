<?php
/**
 * @copyright 2ndInterface 2014 - Robin Breuker
 */


namespace Niborb\FeatureToggle;


use Niborb\FeatureToggle\Entity\Feature;

/**
 * Interface DataProvider
 * @package Niborb\FeatureToggle
 */
interface DataProvider
{

    /**
     * @param $featureName
     * @return null|Feature
     */
    public function fetchFeature($featureName);

    /**
     * @param Feature $feature
     * @return DataProvider
     */
    public function addFeature(Feature $feature);

} 