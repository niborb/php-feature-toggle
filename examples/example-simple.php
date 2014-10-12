<?php
/**
 * @copyright 2ndInterface 2014 - Robin Breuker
 */
require 'vendor/autoload.php';

use Niborb\FeatureToggle\Entity\Feature;
use Niborb\FeatureToggle\Toggle;

$toggle = new Toggle();

$feature = new Feature('user-interface-2.0');
$feature->enable();

// add feature to the toggle manager
$toggle->addFeature($feature);

if ($toggle->isEnabled('user-interface-2.0')) {
    echo "Can see new interface" . PHP_EOL;
} else {
    echo "Cannot(!) see new interface" . PHP_EOL;
}




