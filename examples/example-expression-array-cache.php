<?php
/**
 * @copyright 2ndInterface 2014 - Robin Breuker
 */
require 'vendor/autoload.php';

require 'User.php';

use Doctrine\Common\Cache\ArrayCache;
use Niborb\FeatureToggle\Cache\CacheProxy;
use Niborb\FeatureToggle\Entity\Feature;
use Niborb\FeatureToggle\Toggle;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$toggle = new Toggle();
$toggle->setExpressionLanguage(
    new ExpressionLanguage(
        new CacheProxy(
            new ArrayCache()
        )
    )
);

// new user interface only available for users with an ID in range (1000-2000)
$feature = new Feature('user-interface-2.0');
$feature->enable();
$feature->setExpression('user.getId() in 1000..2000');

// add user to the toggle manager
$toggle->addFeature($feature);

// some users as context
$userOne = new User(3000);
$userTwo = new User(1500);

// check for both users if the feature is enabled

foreach ([$userOne, $userTwo] as $user) {
    if ($toggle->isEnabled('user-interface-2.0', ['user' => $user])
    ) {
        echo "User " . $user->getId() . " can see new interface" . PHP_EOL;
    } else {
        echo "User " . $user->getId() . " cannot(!) see new interface" . PHP_EOL;
    }
}



