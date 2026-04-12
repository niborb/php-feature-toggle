<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/User.php';

use Niborb\FeatureToggle\Entity\Feature;
use Niborb\FeatureToggle\Toggle;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

// ExpressionLanguage caches parsed expressions in a PSR-6 cache pool.
$toggle = new Toggle(
    new ExpressionLanguage(new ArrayAdapter()),
);

// New user interface only available for users with an ID in range (1000-2000).
$feature = (new Feature('user-interface-2.0'))
    ->enable()
    ->setExpression('user.getId() in 1000..2000');

$toggle->addFeature($feature);

$users = [new User(3000), new User(1500)];

foreach ($users as $user) {
    if ($toggle->isEnabled('user-interface-2.0', ['user' => $user])) {
        echo 'User ' . $user->getId() . ' can see new interface' . PHP_EOL;
    } else {
        echo 'User ' . $user->getId() . ' cannot(!) see new interface' . PHP_EOL;
    }
}
