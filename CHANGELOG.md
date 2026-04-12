# Changelog

All notable changes to `niborb/php-feature-toggle` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- PHP 8.2 / 8.3 / 8.4 support.
- `declare(strict_types=1);` and full parameter / return types across the
  source.
- PHPUnit 11 test suite under `tests/`.
- PHPStan 2.x static analysis at level 10.
- GitHub Actions CI (PHPUnit matrix on PHP 8.2/8.3/8.4 + PHPStan job).
- `.editorconfig` for consistent editor settings.
- Dependabot config for automated dependency updates.

### Changed
- **BC break:** minimum PHP version is now `^8.2` (was `>=5.5`).
- **BC break:** `Toggle` now uses constructor injection only:
  ```php
  new Toggle(?ExpressionLanguage $el = null, ?DataProvider $dp = null);
  ```
  The previous `Toggle::setExpressionLanguage()` and `Toggle::setDataProvider()`
  methods have been removed.
- **BC break:** `Toggle`, `Feature`, and `ArrayDataProvider` are now `final`.
  Extend behavior by implementing `FeatureInterface` /
  `FeatureExpressionInterface` / `DataProvider` instead of subclassing.
- **BC break:** `Toggle::isEnabled()` now catches `\Throwable` (was `\Exception`)
  when wrapping expression-evaluation failures into `\RuntimeException`.
- `symfony/expression-language` requirement: `^2.5` → `^6.4 || ^7.0`.
- All public methods now have native parameter and return types.

### Removed
- **BC break:** `Niborb\FeatureToggle\Cache\CacheProxy` has been removed.
  Modern Symfony `ExpressionLanguage` accepts a PSR-6
  `Psr\Cache\CacheItemPoolInterface` directly via its constructor — no proxy
  needed:
  ```php
  use Symfony\Component\Cache\Adapter\ArrayAdapter;
  use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

  new ExpressionLanguage(new ArrayAdapter());
  ```
- **BC break:** the `doctrine/cache` runtime dependency has been dropped.
- The legacy phpspec test suite (`spec/`) has been replaced with PHPUnit tests
  (`tests/`).
- `.travis.yml` has been replaced with GitHub Actions
  (`.github/workflows/ci.yml`).

### Migration guide

If you were using the v0.x API:

```php
// Before
$toggle = new Toggle();
$toggle->setExpressionLanguage(
    new ExpressionLanguage(new CacheProxy(new ArrayCache()))
);
$toggle->setDataProvider($myProvider);

// After
$toggle = new Toggle(
    new ExpressionLanguage(new ArrayAdapter()),
    $myProvider,
);
```

Custom subclasses of `Toggle`, `Feature`, or `ArrayDataProvider` no longer
work. Implement the corresponding interfaces instead.
