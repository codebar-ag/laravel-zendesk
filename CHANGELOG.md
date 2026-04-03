# Changelog

All notable changes to `laravel-zendesk` will be documented in this file.

## 13.0.0 - 2026-04-03

### Breaking changes

- **Laravel 12 and below** are no longer supported. This release targets **Laravel 13** only (`illuminate/contracts` ^13.0).
- **PHP** minimum is now **8.3** (aligned with Laravel 13: 8.3–8.5).
- **Saloon** is **v4** only (`saloonphp/saloon` ^4.0, `saloonphp/laravel-plugin` ^4.0). Saloon 3 is dropped.
- The Laravel facade alias was renamed from `Flatfox` to **`Zendesk`**. Update any `Flatfox::` references to `Zendesk::`.

### Changed

- Raised dev tooling for Laravel 13: `orchestra/testbench` ^11, `pestphp/pest` ^4, `larastan/larastan` ^3.9+, PHPUnit 12 (via Pest).
- Dependency refresh across `composer.json` / `composer.lock` (Guzzle, Spatie packages, PHPStan stack, etc.).

### Release tagging

- Ship this line as **`v13.0.0`** (semver major). When merging to `main`, include **`#major`** in the merge (or squash) commit message so the release workflow tags a major version instead of the default minor bump.
