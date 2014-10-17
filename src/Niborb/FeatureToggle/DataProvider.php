<?php
/**
 * @copyright 2ndInterface 2014 - Robin Breuker
 */


namespace Niborb\FeatureToggle;


use Niborb\FeatureToggle\Entity\FeatureInterface;

/**
 * Interface DataProvider
 * @package Niborb\FeatureToggle
 */
interface DataProvider
{

    /**
     * @param $featureName
     * @return null|FeatureInterface
     */
    public function fetchFeature($featureName);

    /**
     * @param FeatureInterface $feature
     * @return DataProvider
     */
    public function addFeature(FeatureInterface $feature);

} 