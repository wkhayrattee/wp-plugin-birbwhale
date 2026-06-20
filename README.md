# BirbWhale — DeepSeek for WordPress AI

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
│   │   ├── SettingsPage.php          Status page + enable/disable toggle
│   │   └── LogPage.php               Error log viewer
│   ├── Provider/
│   │   └── DeepSeekProvider.php      Provider (identity, base URL, logo)
│   ├── Models/
│   │   └── DeepSeekTextGenerationModel.php   Chat Completions model
│   ├── Metadata/
│   │   └── DeepSeekModelMetadataDirectory.php  /models listing + capability mapping
│   └── birbwhale-plugin-helper.php   Global birbwhale_log() helper
└── views/admin/                      Templates (no inline HTML in PHP classes)
```

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

## DeepSeek model mapping

DeepSeek's `/models` endpoint (like OpenAI's) does not report capabilities, so they are mapped by id:

- `deepseek-chat` (DeepSeek-V3) → text generation + chat history, full option set (sampling, stop, penalties, logprobs, function declarations, JSON output).
- `deepseek-reasoner` (DeepSeek-R1) → text generation + chat history, reduced option set (R1 ignores sampling params and does not support tools/JSON output). Its `reasoning_content` is surfaced as a "thought" message part by the SDK base class.

## Hooks

**Actions:** `birbwhale_loaded`, `birbwhale_deactivating`, `birbwhale_before_register_provider`, `birbwhale_after_register_provider`, `birbwhale_log_cleared`

**Filters:** `birbwhale_default_settings`, `birbwhale_sanitized_settings`, `birbwhale_admin_capability`, `birbwhale_template_args`, `birbwhale_log_file_path`, `birbwhale_log_max_lines`, `birbwhale_uninstall_options`

## Trademarks & attribution

"DeepSeek" and the DeepSeek logo are trademarks of their respective owner. The bundled DeepSeek icon (`assets/images/deepseek.svg`) is the dolphin mark cropped from DeepSeek's official logo (wordmark removed), sourced from <https://github.com/deepseek-ai/DeepSeek-Coder-V2/blob/main/figures/logo.svg>. It is used for identification purposes only; all rights remain with DeepSeek.

BirbWhale is an independent, third-party plugin and is **not** affiliated with, endorsed by, or sponsored by DeepSeek (or OpenAI, Anthropic, Google, or any other provider). Provider names and marks belong to their respective owners.

This plugin is provided "as is", without warranty of any kind. To the fullest extent permitted by law, the authors and contributors accept no liability for any damages or losses arising from its use, the use of any third-party AI provider through it, or the use of any provider name or logo. Your use of any AI provider is subject to that provider's own Terms of Use and Privacy Policy.

## License

GPL-3.0-only. See [LICENSE](LICENSE).
