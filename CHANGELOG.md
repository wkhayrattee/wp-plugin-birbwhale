# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-06-21 ##

### Added ###
* (provider) DeepSeek connector for the WordPress AI Client — registers the DeepSeek provider so it appears automatically on **Settings → Connectors** with a core-managed API key. Available models are discovered live from DeepSeek's API (no model names are hard-coded), so current and future models appear automatically.
* (provider) OpenAI-compatible implementation: text generation via the `chat/completions` endpoint and model discovery via `/models`, extending the WordPress AI Client's `AbstractApiProvider` and `AbstractOpenAiCompatible*` base classes. DeepSeek's reasoning ("thinking") output (`reasoning_content`) is surfaced as a "thought" message part.
* (admin) Branded single-menu **app shell** (header bar + in-page sidebar + content panel) styled after the CaptainBirb theme (navy/teal, serif wordmark), routing Dashboard / Settings / Log via the `view` query arg.
* (admin) Settings section with an enable/disable toggle and a live status panel (AI Client detected, connector enabled, API key set).
* (admin) Error log page with memory-efficient tail reading (`fseek`) and a nonce-protected clear action.
* (branding) Plugin icon (CaptainBirb owl + DeepSeek dolphin badge), a 20px admin-menu glyph, and a WordPress.org banner.
* (lifecycle) Full uninstall cleanup — plugin settings, the DeepSeek API key option, transients, and the log file.
* (extensibility) Action and filter hooks (`birbwhale_*`) across registration, settings, the shell nav, and logging.

[1.0.0]: https://github.com/wkhayrattee/wp-plugin-birbwhale/releases/tag/1.0.0
