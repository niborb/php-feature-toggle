<?php
/**
 * @copyright 2ndInterface 2014 - Robin Breuker
 */
require 'vendor/autoload.php';

use Niborb\FeatureToggle\Entity\Feature;
use Niborb\FeatureToggle\Toggle;

$toggle = new Toggle();

/** @var $features Feature[]*/
$features = [
    new Feature('user-interface-2.0'),
    new Feature('fancy-feature-x'),
    new Feature('fancy-feature-y')
];

foreach ($features as $feature) {
    $feature->enable();
    $toggle->addFeature($feature);
}

foreach (['user-interface-2.0', 'fancy-feature-x', 'fancy-feature-y'] as $featureName) {
    if ($toggle->isEnabled($featureName)) {
        echo "Feature {$featureName} is enabled" . PHP_EOL;
    }
}




