# CLAUDE.md

Guidance for Claude Code (and other AI assistants) when working in this repository.

## Project overview

`niborb/php-feature-toggle` is a small PHP 5 feature toggle library. It lets an
application register named features, enable/disable them, and optionally gate
them behind a Symfony ExpressionLanguage expression that is evaluated against a
runtime context (e.g. the current user). Parsed expressions can be cached via a
Doctrine Cache adapter.

- License: MIT
- Package name: `niborb/php-feature-toggle`
- PHP requirement: `>=5.5` (tested on 5.5 and 5.6 on Travis)
- Autoload: PSR-4, `Niborb\FeatureToggle\` → `src/Niborb/FeatureToggle`

This is a legacy PHP 5.5/5.6 codebase. Keep changes compatible with that
baseline unless the user explicitly asks to modernize it — do **not**
introduce PHP 7+ syntax (scalar type hints, return types, nullable types,
`??`, arrow functions, typed properties, etc.).

## Dependencies

Runtime (`composer.json`):
- `symfony/expression-language: ^2.5` — evaluates the feature expressions.
- `doctrine/cache: ^1.4` — used via `CacheProxy` to cache parsed expressions.

Dev:
- `phpspec/phpspec: ~2.0` — the test framework.
- `henrikbjorn/phpspec-code-coverage` — HTML + clover coverage output.
- `satooshi/php-coveralls` — uploads coverage to Coveralls on CI.

## Repository layout

```
src/Niborb/FeatureToggle/
├── Toggle.php                     # Main entry point / facade
├── DataProvider.php               # Interface for feature storage
├── DataProvider/
│   └── ArrayDataProvider.php      # In-memory implementation (default)
├── Entity/
│   ├── FeatureInterface.php       # name/enable/disable/isEnabled
│   ├── FeatureExpressionInterface.php  # adds get/setExpression
│   └── Feature.php                # Default implementation
└── Cache/
    └── CacheProxy.php             # Doctrine Cache → Symfony ParserCacheInterface adapter

spec/Niborb/FeatureToggle/          # phpspec specs mirror src/ layout
├── ToggleSpec.php
├── Cache/CacheProxySpec.php
├── DataProvider/ArrayDataProviderSpec.php
└── Entity/FeatureSpec.php

examples/                           # Runnable usage examples
├── example-simple.php
├── example-multiple-features.php
├── example-expression-array-cache.php
└── User.php
```

## Core architecture

`Toggle` (`src/Niborb/FeatureToggle/Toggle.php`) is the facade:

- Holds an optional `ExpressionLanguage` (injected via `setExpressionLanguage`)
  and a `DataProvider` (injected via `setDataProvider`, defaults to a new
  `ArrayDataProvider` lazily in `getDataProvider()`).
- `addFeature(FeatureInterface)` / `hasFeature(FeatureInterface)` delegate to
  the data provider.
- `isEnabled(string $featureName, array $context = [])`:
  1. Fetches the feature from the data provider. Unknown feature → `false`.
  2. Returns `false` if `$feature->isEnabled()` is false.
  3. If the feature implements `FeatureExpressionInterface` and has a
     non-empty expression, evaluates it via `ExpressionLanguage` with the
     provided `$context`. Any evaluation exception is wrapped and re-thrown
     as `\RuntimeException`.
- Features **without** an expression (or when no `ExpressionLanguage` is set)
  skip validation and rely purely on the boolean `enabled` flag.

`Feature` implements `FeatureExpressionInterface` (which extends
`FeatureInterface`). Its constructor throws `\LogicException` when the name is
empty/whitespace. `enable()`/`disable()`/`setExpression()` all return `$this`
for fluent chaining.

`ArrayDataProvider` stores features in an associative array keyed by name and
implements `\IteratorAggregate`. `addFeatures(array)` throws `\LogicException`
for non-`FeatureInterface` items.

`CacheProxy` adapts a `Doctrine\Common\Cache\Cache` to Symfony's
`ParserCacheInterface` so parsed expressions can be cached between requests.
Note `fetch()` converts Doctrine's `false` miss into `null` as required by the
Symfony interface.

### Conventions / style

- PSR-4 layout under `src/Niborb/FeatureToggle`.
- Namespaces mirror directories; specs live under `spec\Niborb\FeatureToggle`.
- PHPDoc blocks on all public methods (param/return/throws). Keep them in
  sync when modifying signatures.
- Fluent setters (`return $this`) are used throughout — preserve this when
  adding new setters.
- Prefer throwing `\LogicException` for misuse and `\RuntimeException` for
  runtime failures (matches existing usage in `Feature` and `Toggle`).
- Array short syntax `[]` is used (fine on PHP 5.5+).
- No strict types, no scalar/return type hints — PHP 5.5 compatibility.

## Development workflow

Install deps:

```
composer install
```

Run the test suite:

```
vendor/bin/phpspec run
```

This is the same command CI (`.travis.yml`) runs. Code coverage (HTML +
clover) is configured in `phpspec.yml` and written to `coverage/` and
`build/logs/clover.xml`. An alternate `phpspec_nocc.yml` exists (currently
empty) as a config file for running specs without the coverage extension — use
`vendor/bin/phpspec run -c phpspec_nocc.yml` if the coverage extension is not
installed locally.

Generate a new spec/class pair via phpspec's describe/run cycle:

```
vendor/bin/phpspec describe 'Niborb/FeatureToggle/SomeClass'
vendor/bin/phpspec run
```

There is no linter/formatter configured in the repo.

### Adding a new feature class / data provider

1. Create the class under `src/Niborb/FeatureToggle/...` in the matching
   namespace.
2. If it represents a feature, implement `FeatureInterface` (or
   `FeatureExpressionInterface` if it exposes an expression).
3. If it stores features, implement `DataProvider`.
4. Add a matching phpspec under `spec/Niborb/FeatureToggle/...` named
   `<Class>Spec` and make it extend `PhpSpec\ObjectBehavior`. Mirror the
   existing specs' style (`it_is_initializable`, `it_...` method names).
5. Run `vendor/bin/phpspec run` and ensure it passes before committing.

## Usage snapshot

Minimal (no expression language):

```php
$toggle = new Niborb\FeatureToggle\Toggle();
$feature = (new Niborb\FeatureToggle\Entity\Feature('ui-2.0'))->enable();
$toggle->addFeature($feature);
$toggle->isEnabled('ui-2.0'); // true
```

With an expression and context (see README / `examples/`):

```php
$toggle->setExpressionLanguage(
    new ExpressionLanguage(new CacheProxy(new ArrayCache()))
);
$feature->setExpression('user.getId() in 1000..2000');
$toggle->isEnabled('ui-2.0', ['user' => $user]);
```

If a feature is enabled and has a non-empty expression but no
`ExpressionLanguage` has been set on the `Toggle`, validation is skipped and
the feature is treated as enabled (see `Toggle::validate`).

## Gotchas

- `Toggle::isEnabled` wraps any `ExpressionLanguage::evaluate` exception in a
  `\RuntimeException('Could not evaluate expression ...')`. Tests rely on this
  behavior — keep it when refactoring.
- `ArrayDataProvider::addFeatures` uses `array_walk` with a `$this`-capturing
  closure. This relies on PHP 5.4+ closure `$this` binding; keep the callable
  form if touching it.
- Unknown feature names return `false` from `isEnabled` rather than throwing
  (`it_should_ignore_unknown_features_and_handles_them_as_disabled_features`).
- `Feature` constructor trims the name and rejects empty strings — preserve
  this invariant; `FeatureSpec` covers it.

## Git / branch conventions for AI assistants

- Develop on the branch specified in the session instructions (currently
  `claude/add-claude-documentation-4PnF3`). Do not push to `master`.
- Create NEW commits rather than amending.
- Only open a pull request when the user explicitly asks for one.
