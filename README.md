## PHP feature toggle library

A small, dependency-light feature toggle library for PHP 8.2+. Features can be
toggled on/off and optionally gated behind a Symfony ExpressionLanguage
expression evaluated against a runtime context.

### Requirements

- PHP `^8.2`
- `symfony/expression-language` `^6.4 || ^7.0`

### Installation

```bash
composer require niborb/php-feature-toggle
```

See also [packagist.org/packages/niborb/php-feature-toggle](https://packagist.org/packages/niborb/php-feature-toggle).

### Quick start

```php
<?php

require 'vendor/autoload.php';

use Niborb\FeatureToggle\Entity\Feature;
use Niborb\FeatureToggle\Toggle;

$toggle = new Toggle();

$feature = (new Feature('user-interface-2.0'))->enable();
$toggle->addFeature($feature);

if ($toggle->isEnabled('user-interface-2.0')) {
    echo 'New UI enabled' . PHP_EOL;
}
```

### Expression-based toggles

Pass a Symfony `ExpressionLanguage` instance to the `Toggle` constructor and
attach an expression to a feature. The expression is evaluated against the
context array passed to `isEnabled()`.

```php
<?php

require 'vendor/autoload.php';
require 'User.php';

use Niborb\FeatureToggle\Entity\Feature;
use Niborb\FeatureToggle\Toggle;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$toggle = new Toggle(
    new ExpressionLanguage(new ArrayAdapter()), // optional PSR-6 cache pool
);

$feature = (new Feature('user-interface-2.0'))
    ->enable()
    ->setExpression('user.getId() in 1000..2000');

$toggle->addFeature($feature);

foreach ([new User(3000), new User(1500)] as $user) {
    $enabled = $toggle->isEnabled('user-interface-2.0', ['user' => $user]);
    echo "User {$user->getId()} " . ($enabled ? 'can' : 'cannot') . " see new interface" . PHP_EOL;
}
```

Output:

```
User 3000 cannot see new interface
User 1500 can see new interface
```

More runnable examples in [`examples/`](examples/).

### Custom data providers

`Toggle` defaults to an in-memory `ArrayDataProvider`. You can plug in any
implementation of `Niborb\FeatureToggle\DataProvider`:

```php
$toggle = new Toggle(null, new MyDatabaseDataProvider($pdo));
```

### Development

```bash
composer install
composer test       # PHPUnit
composer phpstan    # PHPStan (level 8)
```

### License

MIT — see [LICENSE](LICENSE).
