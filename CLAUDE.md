# CLAUDE.md

Guidance for Claude Code (and other AI assistants) when working in this repository.

## Project overview

`niborb/php-feature-toggle` is a small PHP feature toggle library. It lets an
application register named features, enable/disable them, and optionally gate
them behind a Symfony ExpressionLanguage expression that is evaluated against a
runtime context (e.g. the current user).

- License: MIT
- Package name: `niborb/php-feature-toggle`
- PHP requirement: `^8.2` (CI matrix runs 8.2, 8.3, 8.4)
- Autoload: PSR-4, `Niborb\FeatureToggle\` → `src/Niborb/FeatureToggle/`
- Tests autoload (dev): `Niborb\FeatureToggle\Tests\` → `tests/`

The codebase was modernized from a legacy PHP 5.5/5.6 codebase. All source files
use `declare(strict_types=1);`, parameter and return type hints, readonly
properties where appropriate, and constructor property promotion. The three
concrete classes (`Toggle`, `Feature`, `ArrayDataProvider`) are `final` —
extend behavior by implementing `FeatureInterface` /
`FeatureExpressionInterface` / `DataProvider` instead of subclassing.

## Dependencies

Runtime (`composer.json`):
- `symfony/expression-language: ^6.4 || ^7.0` — evaluates feature expressions.
  Accepts an optional PSR-6 `CacheItemPoolInterface` for caching parsed
  expressions; this library no longer ships its own cache adapter.

Dev:
- `phpunit/phpunit: ^11.0` — test framework.
- `phpstan/phpstan: ^2.1` — static analysis at `level: 10` (the strictest).
- `symfony/cache: ^6.4 || ^7.0` — used by examples for the PSR-6 cache pool.

## Repository layout

```
src/Niborb/FeatureToggle/
├── Toggle.php                     # Main facade
├── DataProvider.php               # Interface for feature storage
├── DataProvider/
│   └── ArrayDataProvider.php      # In-memory implementation (default)
└── Entity/
    ├── FeatureInterface.php       # name/enable/disable/isEnabled
    ├── FeatureExpressionInterface.php  # adds get/setExpression
    └── Feature.php                # Default implementation

tests/                              # PHPUnit tests mirror src/ layout
├── ToggleTest.php
├── DataProvider/ArrayDataProviderTest.php
└── Entity/FeatureTest.php

examples/                           # Runnable usage examples
├── example-simple.php
├── example-multiple-features.php
├── example-expression-with-cache.php
└── User.php

.github/
├── workflows/ci.yml                # GitHub Actions CI (PHPUnit + PHPStan)
└── dependabot.yml                  # Composer + GitHub Actions dependency updates
phpunit.xml.dist                    # PHPUnit config
phpstan.neon.dist                   # PHPStan config (level 10, src + tests)
.editorconfig
CHANGELOG.md
composer.json
```

## Core architecture

`Toggle` (`src/Niborb/FeatureToggle/Toggle.php`) is the facade:

- Constructor takes an optional `ExpressionLanguage` and an optional
  `DataProvider`. Both are injected via constructor only — there are no
  setters. If no data provider is given, a fresh `ArrayDataProvider` is used.
- `addFeature(FeatureInterface)` / `hasFeature(FeatureInterface)` delegate to
  the data provider.
- `isEnabled(string $featureName, array $context = [])`:
  1. Fetches the feature. Unknown feature → `false`.
  2. Returns `false` if `$feature->isEnabled()` is false.
  3. If the feature implements `FeatureExpressionInterface` and has a
     non-empty expression **and** an `ExpressionLanguage` was injected,
     evaluates the expression against `$context`. Any `\Throwable` from the
     evaluator is wrapped and re-thrown as `\RuntimeException('Could not
     evaluate expression …')`.
  4. If the feature has an expression but no `ExpressionLanguage` was injected,
     validation is skipped and the feature is treated as enabled. Tests in
     `tests/ToggleTest.php` cover this branch.

`Feature` implements `FeatureExpressionInterface` (which extends
`FeatureInterface`). Constructor throws `\LogicException` if the trimmed name
is empty. The `name` is `readonly`; `enabled` and `expression` are mutable
through fluent `enable()`/`disable()`/`setExpression()` returning `static`.

`ArrayDataProvider` stores features in `array<string, FeatureInterface>` keyed
by name and implements `\IteratorAggregate<string, FeatureInterface>`.
`addFeatures(iterable)` accepts `iterable<mixed>` and throws `\LogicException`
on non-`FeatureInterface` items (intentional runtime check; PHPDoc is
deliberately loose so the check survives static analysis).

### Public API surface

```php
new Toggle(?ExpressionLanguage $el = null, ?DataProvider $dp = null);

$toggle->addFeature(FeatureInterface): self
$toggle->hasFeature(FeatureInterface): bool
$toggle->isEnabled(string $name, array $context = []): bool

new Feature(string $name);
$feature->enable(): static
$feature->disable(): static
$feature->isEnabled(): bool
$feature->setExpression(string): static
$feature->getExpression(): string
$feature->getName(): string
```

There is no `Toggle::setExpressionLanguage()` or `Toggle::setDataProvider()`
anymore — those existed in the pre-modernization v0.x API and were removed in
favor of constructor injection.

## Conventions

- `declare(strict_types=1);` at the top of every PHP file in `src/` and `tests/`.
- PSR-4 layout under `src/Niborb/FeatureToggle/`.
- Namespaces mirror directories; tests live under `Niborb\FeatureToggle\Tests\`.
- Fluent setters return `static` (or `self` on interfaces returning `self`).
- Throw `\LogicException` for misuse and `\RuntimeException` for runtime
  failures (matches existing usage in `Feature` and `Toggle`).
- Prefer `readonly` for properties that are not mutated after construction.
- Use constructor property promotion when it does not hurt readability
  (`Toggle` uses it; `Feature` does not, because the constructor performs
  validation).
- Keep PHPDoc only where it adds information beyond the type hints (generics,
  array shapes, `@throws`).

## Development workflow

Install dependencies:

```bash
composer install
```

Run the test suite:

```bash
composer test          # → vendor/bin/phpunit
```

Run static analysis:

```bash
composer phpstan       # → vendor/bin/phpstan analyse
```

CI (`.github/workflows/ci.yml`) runs PHPUnit on PHP 8.2/8.3/8.4 and PHPStan
once on PHP 8.3.

### Adding a new feature class / data provider

1. Create the class under `src/Niborb/FeatureToggle/...` in the matching
   namespace, with `declare(strict_types=1);` and full type hints.
2. If it represents a feature, implement `FeatureInterface` (or
   `FeatureExpressionInterface` if it exposes an expression).
3. If it stores features, implement `DataProvider`.
4. Add a matching PHPUnit test under `tests/...` named `<Class>Test`,
   extending `PHPUnit\Framework\TestCase`. Use `final class …`.
5. When mocking, mock the interface (`DataProvider`, `FeatureInterface`,
   etc.) — never the concrete final classes; PHPUnit cannot double them.
6. Run `composer test && composer phpstan` and ensure both pass.
7. Add a `## [Unreleased]` entry to `CHANGELOG.md`.

## Gotchas

- `Toggle::isEnabled` wraps any `ExpressionLanguage::evaluate` exception
  (catches `\Throwable`) in `\RuntimeException('Could not evaluate expression
  …')`. Tests rely on this behavior — preserve it when refactoring.
- Unknown feature names return `false` from `isEnabled` rather than throwing.
- `Feature` constructor trims the name and rejects empty/whitespace strings —
  preserve this invariant; `FeatureTest` covers it.
- `ArrayDataProvider::addFeatures` accepts `iterable<mixed>` and validates each
  item at runtime. Do not tighten the PHPDoc to `iterable<FeatureInterface>` —
  PHPStan would then flag the runtime guard as dead code.
- The legacy `CacheProxy` class (Doctrine cache → Symfony
  `ParserCacheInterface`) was removed. Modern Symfony ExpressionLanguage
  accepts a PSR-6 `CacheItemPoolInterface` directly via its constructor, e.g.
  `new ExpressionLanguage(new ArrayAdapter())`.

## Git / branch conventions for AI assistants

- Develop on the branch specified in the session instructions. Do not push to
  `master` directly.
- Create NEW commits rather than amending.
- Only open a pull request when the user explicitly asks for one.
