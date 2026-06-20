=== BirbWhale — DeepSeek for WordPress AI ===
Contributors: wkhayrattee
Tags: ai, deepseek, ai-client, connector, reasoning
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 8.4
Stable tag: 1.0.0
License: GPL-3.0-only
License URI: https://www.gnu.org/licenses/gpl-3.0.html

DeepSeek AI connector for the WordPress AI Client - adds DeepSeek text generation and reasoning to WordPress.

== Description ==

BirbWhale adds **DeepSeek** as a provider for the WordPress AI Client introduced in WordPress 7.0. Once active, DeepSeek appears on **Settings → Connectors** alongside the providers shipped by WordPress (such as Anthropic, Google, and OpenAI), and any plugin built on the AI Client can use DeepSeek models.

DeepSeek exposes an OpenAI-compatible API, so BirbWhale plugs straight into the same interface used by core and other connectors.

= Models =

BirbWhale does not hard-code model names. It lists whatever your DeepSeek account exposes via the API, so current and future DeepSeek models appear automatically on the Connectors screen. DeepSeek's reasoning ("thinking") output is surfaced as a separate "thought" part.

= How it works =

WordPress core owns the API key. BirbWhale registers the DeepSeek provider; WordPress then shows a DeepSeek card on **Settings → Connectors**, stores the key, and hands it to the AI Client at request time. BirbWhale adds a small status page (enable/disable the connector, see whether a key is set) and an error log for diagnostics.

= External Services =

This plugin enables WordPress to communicate with the **DeepSeek API** when a site administrator configures a DeepSeek API key and the WordPress AI Client (core or another plugin) requests a generation from a DeepSeek model.

* **What it does:** registers the DeepSeek provider so the AI Client can send prompts to DeepSeek and receive generated text.
* **Data sent:** the prompt/messages, model configuration (such as temperature and max tokens), and your DeepSeek API key, sent to DeepSeek's API endpoints (`https://api.deepseek.com/chat/completions` and `https://api.deepseek.com/models`). Requests are only made when you have entered a DeepSeek API key and an AI Client request targets a DeepSeek model.
* **When:** only on requests that use a DeepSeek model. The plugin makes no calls on its own.
* **Service:** DeepSeek
* **Terms of Use:** https://platform.deepseek.com/downloads/DeepSeek%20Open%20Platform%20Terms%20of%20Service.html
* **Privacy Policy:** https://platform.deepseek.com/downloads/DeepSeek%20Privacy%20Policy.html

No data is sent until you provide an API key (opt-in).

= Trademarks & attribution =

"DeepSeek" and the DeepSeek logo are trademarks of their respective owner. The DeepSeek icon bundled with this plugin is derived from DeepSeek's official logo (the dolphin mark, with the wordmark removed), sourced from:
https://github.com/deepseek-ai/DeepSeek-Coder-V2/blob/main/figures/logo.svg

All rights to the DeepSeek name and logo remain with DeepSeek. The mark is used here for identification purposes only, to indicate the provider this connector integrates with.

BirbWhale is an independent, third-party plugin. It is **not** affiliated with, endorsed by, sponsored by, or otherwise associated with DeepSeek (or with OpenAI, Anthropic, Google, or any other provider mentioned). Any provider names and marks are the property of their respective owners.

This plugin is provided "as is", without warranty of any kind, express or implied. To the fullest extent permitted by law, the authors and contributors accept no liability for any claim, damages, costs, or other losses arising from the use of this plugin, the use of any third-party AI provider through it, or the use of any provider name or logo. Your use of any AI provider is subject to that provider's own Terms of Use and Privacy Policy. See also the LICENSE file (GPL-3.0-only), which disclaims warranty and liability.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/birbwhale/`, or install through the WordPress Plugins screen.
2. Activate the plugin through the 'Plugins' screen.
3. Go to **Settings → Connectors**, find DeepSeek, and enter your DeepSeek API key.
4. (Optional) Visit **BirbWhale → Settings** to confirm status or disable the connector without deactivating the plugin.

== Frequently Asked Questions ==

= Where do I enter my API key? =

On **Settings → Connectors**. BirbWhale registers the provider; WordPress core renders the key field and stores the key securely.

= Does this require WordPress 7.0? =

Yes. BirbWhale relies on the WordPress AI Client that ships with WordPress 7.0. On older versions it will not register the provider.

= Which DeepSeek models are supported? =

All text models your DeepSeek account exposes — the list is fetched live from DeepSeek's API, so new models appear automatically. (DeepSeek is deprecating the legacy `deepseek-chat` / `deepseek-reasoner` aliases in favour of its newer models.)

== Support ==

Need a hand? Reach out through either channel — you'll also find these on the **BirbWhale → Get Help** screen in your dashboard:

* **GitHub issues:** https://github.com/wkhayrattee/wp-plugin-birbwhale/issues — best for bugs and feature requests.
* **Email:** birbwhale@id.captainbirb.com

== Changelog ==

= 1.0.0 =
* DeepSeek connector for the WordPress AI Client — DeepSeek appears automatically on Settings → Connectors with a core-managed API key; available models are discovered live from the DeepSeek API.
* Branded single-menu app-shell UI (CaptainBirb navy/teal) with Dashboard, Settings, and Log sections.
* Enable/disable toggle, a live status panel, and a nonce-protected error log.
* Plugin icon (CaptainBirb owl + DeepSeek badge) and banner.
* Full uninstall cleanup and birbwhale_* extensibility hooks.

For a detailed, structured history see CHANGELOG.md in the source repository.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
