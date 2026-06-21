# BirbWhale — AI Provider for DeepSeek

> DeepSeek AI connector for the WordPress AI Client — adds DeepSeek text generation and reasoning to WordPress.

BirbWhale registers **DeepSeek** as a provider for the **WordPress AI Client** introduced in WordPress 7.0. It follows the same pattern as the official `ai-provider-for-*` connectors: it registers a provider, and WordPress core handles the API key on **Settings → Connectors**.

## How it works

WordPress 7.0 bundles the AI Client SDK (`WordPress\AiClient`) in core (`wp-includes/php-ai-client/`). A connector registers a *provider* with the client's default registry:

```php
AiClient::defaultRegistry()->registerProvider(DeepSeekProvider::class);
```

BirbWhale registers on the `init` hook at priority `5` — before core's connector discovery at priority `15`. Because of that, DeepSeek **automatically appears on Settings → Connectors**, where core renders an API-key field, stores the key as `connectors_ai_deepseek_api_key`, and passes it back to the SDK at request time. BirbWhale never handles the API key itself.

DeepSeek exposes an **OpenAI-compatible** REST API, so the DeepSeek classes extend the SDK's OpenAI-compatible base classes and only customize the base URL, model catalog, and capability mapping.

## Architecture

```
birbwhale/
├── birbwhale.php                     Bootstrap: constants, autoloader, lifecycle, boot
├── uninstall.php                     Full data cleanup (incl. the DeepSeek API key)
├── composer.json                     PSR-4 autoloading (vendor-dir: includes/vendor)
├── src/
│   ├── Core/
│   │   ├── Enum.php                  All constants
│   │   ├── PluginManager.php         Orchestrator: lifecycle, hooks, provider registration, menu
│   │   └── Utils.php                 Logging, log rotation, template loader
│   ├── Admin/
│   │   ├── Shell.php                 App-shell: chrome + routing for the single menu
│   │   ├── SettingsPage.php          Settings section (enable toggle + status)
│   │   └── LogPage.php               Log section (error log viewer)
│   ├── Provider/
│   │   └── DeepSeekProvider.php      Provider (identity, base URL, logo)
│   ├── Models/
│   │   └── DeepSeekTextGenerationModel.php   Chat Completions model
│   ├── Metadata/
│   │   └── DeepSeekModelMetadataDirectory.php  /models listing + capability mapping
│   └── birbwhale-plugin-helper.php   Global birbwhale_log() helper
├── views/admin/                      Templates (no inline HTML in PHP classes)
│   ├── shell.php                     Header bar + sidebar + content panel
│   ├── shell-dashboard.php           Dashboard section
│   ├── page-settings.php             Settings section
│   ├── page-log.php                  Log section
│   └── field-enabled.php             Enable-toggle field
└── assets/
    ├── css/admin.css                 Branded app-shell styles (CaptainBirb navy/teal)
    └── images/
        ├── icon.svg                  Plugin icon (owl + whale-tail badge); + PNG renders
        ├── icon-menu.svg             20px admin-menu glyph
        ├── deepseek.svg              DeepSeek dolphin (Connectors card logo)
        └── banner-1544x500.svg       wp.org banner; + PNG renders
```

## Admin UI

All admin screens live under a **single** top-level "BirbWhale" menu, rendered inside a
branded **app shell** (header bar + in-page left sidebar + content panel) styled after the
CaptainBirb theme (navy/teal, serif wordmark). `Shell` owns the chrome and routes the
`?view=` arg (Dashboard / Settings / Log) to each section via buffer-then-wrap; the API key
itself stays on **Settings → Connectors** (core-owned). The menu icon and header mark use the
plugin icon (CaptainBirb owl + whale-tail badge).

## Requirements

- WordPress **7.0+** (for the bundled AI Client)
- PHP **8.4+**

## Build

PSR-4 autoloading is wired through Composer. After cloning, generate the autoloader:

```bash
composer install --no-dev
# or, if dependencies are already in place:
composer dump-autoload --optimize
```

The autoloader lives in `includes/vendor/` and is committed so the plugin runs without a build step on the target site.

## DeepSeek models

Models are discovered dynamically from DeepSeek's `/models` endpoint — BirbWhale hard-codes no model names, so current and future DeepSeek models (e.g. `deepseek-v4-flash`, `deepseek-v4-pro`) appear automatically. The endpoint doesn't report capabilities, so each text model is registered with `textGeneration` + `chatHistory` and the full OpenAI-compatible chat option set (system instruction, sampling, stop sequences, penalties, logprobs, function calling, JSON output). DeepSeek's "thinking"/reasoning output (`reasoning_content`) is surfaced as a "thought" message part by the SDK base class. (DeepSeek is deprecating the legacy `deepseek-chat` / `deepseek-reasoner` aliases, which are sorted last while they remain.)

## Hooks

**Actions:** `birbwhale_loaded`, `birbwhale_deactivating`, `birbwhale_before_register_provider`, `birbwhale_after_register_provider`, `birbwhale_log_cleared`

**Filters:** `birbwhale_default_settings`, `birbwhale_sanitized_settings`, `birbwhale_admin_capability`, `birbwhale_template_args`, `birbwhale_shell_nav`, `birbwhale_log_file_path`, `birbwhale_log_max_lines`, `birbwhale_uninstall_options`

## Trademarks & attribution

"DeepSeek" and the DeepSeek logo are trademarks of their respective owner. DeepSeek's logo (`assets/images/deepseek.svg` — the dolphin from their official logo with the wordmark removed, sourced from <https://github.com/deepseek-ai/DeepSeek-Coder-V2/blob/main/figures/logo.svg>) is shown only on the Settings → Connectors card to identify the provider, as the official AI provider connectors do for theirs. The plugin's own branding (directory icon, admin-menu icon, header mark, banner) is original artwork — a CaptainBirb owl with an original whale-tail motif — not based on DeepSeek's logo.

BirbWhale is an independent, third-party plugin and is **not** affiliated with, endorsed by, or sponsored by DeepSeek (or OpenAI, Anthropic, Google, or any other provider). Provider names and marks belong to their respective owners.

This plugin is provided "as is", without warranty of any kind. To the fullest extent permitted by law, the authors and contributors accept no liability for any damages or losses arising from its use, the use of any third-party AI provider through it, or the use of any provider name or logo. Your use of any AI provider is subject to that provider's own Terms of Use and Privacy Policy.

## License

GPL-3.0-only. See [LICENSE](LICENSE).
