# BirbWhale tests

A layered regression suite — run the layers relevant to your change. None of these
files ship in the release zip (they're excluded in `.github/workflows/release.yml`).

## 1. Unit tests (PHPUnit) — no DB, no network

The core regression suite: the DeepSeek **provider**, **model**, and **/models
parsing / capability mapping / sort order**.

**Requires PHPUnit 11+.** Provide it either way:
- a `phpunit` on your `PATH` (a `phpunit.phar` or a global install), or
- `composer require --dev phpunit/phpunit` (then run `composer install --no-dev`
  before building a release, so PHPUnit doesn't end up in the shipped `includes/vendor/`).

**The SDK:** `tests/bootstrap.php` loads the WordPress AI Client from a Composer copy
if present, otherwise from a local WordPress-core copy. In this workspace it
auto-finds `../wordpress/wp-includes/php-ai-client/`. Override with
`BIRBWHALE_SDK_AUTOLOAD=/abs/path/to/php-ai-client/autoload.php`.

```bash
composer dump-autoload --optimize     # once, so BirbWhale\* classes autoload
phpunit                                # or: composer test
# explicit SDK path:
BIRBWHALE_SDK_AUTOLOAD=/abs/.../php-ai-client/autoload.php phpunit
```

## 2. WordPress integration smoke — DB, no network

Provider registration, the core Connectors card + logo URL, the admin shell views,
and bundled assets. Needs the plugin active on a running WordPress (LocalWP MySQL up):

```bash
wp plugin activate birbwhale
wp eval-file tests/wp-cli/smoke.php
```

## 3. Live DeepSeek end-to-end — DB + network + a saved API key

Makes small **real** calls to DeepSeek using the key from **Settings → Connectors**
(live `/models` check + one text generation):

```bash
wp eval-file tests/wp-cli/live-deepseek.php
```

## 4. Compliance — WordPress.org Plugin Check

```bash
wp plugin check birbwhale \
  --categories=general,plugin_repo,security,performance,accessibility
```

Only `.github` / `.gitignore` should remain (both stripped from the release zip).
