<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Niborb\FeatureToggle\Entity\Feature;
use Niborb\FeatureToggle\Toggle;

$toggle = new Toggle();

$feature = (new Feature('user-interface-2.0'))->enable();

$toggle->addFeature($feature);

if ($toggle->isEnabled('user-interface-2.0')) {
    echo 'Can see new interface' . PHP_EOL;
} else {
    echo 'Cannot(!) see new interface' . PHP_EOL;
}
