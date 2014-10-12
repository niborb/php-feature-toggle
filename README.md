## PHP 5.5 feature toggle library

### Installation

You can install the library with Composer.

    composer.phar require "niborb/php-feature-toggle"

### Example (see also the examples directory)

```php
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

Output:

    User 3000 cannot(1) see new interface
    User 1500 can see new interface


### PHPSpec

The library contains PHPSpec tests (./spec). Clone the repository and run:

    vendor/bin/phpspec run