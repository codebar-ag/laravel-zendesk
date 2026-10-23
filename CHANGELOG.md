# Changelog

All notable changes to `laravel-zendesk` will be documented in this file.

## 14.0.0 - 2026-10-23

Zendesk is [removing API tokens](https://support.zendesk.com/hc/en-us/articles/10851263566234) for the Support APIs. No new API tokens can be created after 2026-10-27, and all existing tokens stop working on 2027-04-30. This release switches the package to OAuth 2.0. See [UPGRADING.md](UPGRADING.md).

### Breaking changes

- **PHP 8.5** only.
- **Authentication:** The `token` (API token) and `basic` (email + password) methods are removed, together with the `email_address`, `password` and `api_token` config keys and `ZendeskConnector::setAuth()`.
- **Authentication:** The default method is now `oauth`. `access_token` is the only other method.
- **Config:** `config/zendesk.php` has a new structure (`auth.oauth.*`, `auth.access_token`, `cache.*`). Republish the config if you published it before.
- **Exceptions:** Configuration errors throw `ZendeskConfigurationException` instead of a generic `\Exception` with code 500. Both new package exceptions extend `ZendeskException`.
- **DTOs:** Calling `->dto()` on a failed response throws Saloon's `RequestException` (e.g. `NotFoundException`) instead of a generic `\Exception`.
- **DTOs:** `AllTicketsDTO::$tickets` now contains `SingleTicketDTO` objects instead of arrays.
- **DTOs:** Missing dates (`created_at`, `updated_at`, `due_at`, …) are `null`. Before, `Carbon::parse(null)` silently filled them with the current time.
- **DTOs:** `AttachmentDTO`/`ThumbnailDTO` `width` and `height` are now `?int`. `CommentDTO::$plain_body` is now `?string`. `UploadDTO::$expires_at` and `UploadDTO::$attachment` are nullable.
- **Requests:** `CreateSingleTicketRequest` sends only the attributes that are set (no `null` values) and posts to `/tickets.json`. The constructor parameter is renamed from `$createTicket` to `$ticket`.
- **Connector:** The `Content-Type: application/json` default header is removed from the connector. Requests with a body set their own content type.
- **Dependencies:** `saloonphp/laravel-plugin` ^5.0, `saloonphp/saloon` ^4.5 and `pestphp/pest` ^5.0. The unused `saloonphp/cache-plugin`, the direct `guzzlehttp/guzzle` requirement and `spatie/laravel-ray` were dropped.

### Added

- OAuth 2.0 **client credentials** authentication. Tokens are requested from `/oauth/tokens`, cached with a lock, renewed before they expire, and a request that gets a `401` is retried once with a fresh token.
- `access_token` method, and support for `$connector->authenticate(new TokenAuthenticator(...))` at runtime.
- `OAuthCredentials`, `AccessTokenRepository`, `ZendeskOAuthConnector`, `CreateAccessTokenRequest` and `AccessTokenDTO`.
- `new ZendeskConnector(subdomain: ..., credentials: ...)` and `Zendesk::connector(...)` for multiple Zendesk accounts.
- `CreateAttachmentRequest` accepts an optional upload `token` to add files to an existing upload. The file name is now URL-encoded.
- `SingleTicketDTO::$custom_status_id`, `SingleTicketDTO::$generated_timestamp` and `SingleTicketDTO::toRequestArray()`.
- The subdomain is validated, so a misconfigured value cannot change the API host.

### Fixed

- `ZendeskAuthenticationException` handles non-JSON error responses from the token endpoint.
- `CommentDTO` no longer throws a `TypeError` for comments with `created_at` or a string `plain_body`.
- `UploadDTO` no longer throws when the upload has no attachments.
- `AttachmentDTO`/`ThumbnailDTO` no longer pass `null` to `MalwareScanResult::tryFrom()`.

### Changed

- Live tests (`tests/Live`, `composer test:record`) record real Zendesk responses as redacted Saloon fixtures and replay them offline. The fixtures shipped with this release are hand-written placeholders until they are recorded against a live account. Tests use the `Saloon::fake()` facade.
- `AllTicketsRequest` accepts `perPage`, `page`, `sortBy` and `sortOrder`.
- PHPStan level raised from 5 to 8. The test suite blocks stray HTTP requests. 133 Pest 5 tests (including 7 live tests replaying fixtures) cover authentication, token caching, retries, requests and DTOs.
- CI runs on PHP 8.5 only. The stale `.php-cs-fixer.dist.php` was removed, and `composer format` now runs Pint.

### Release tagging

- Ship this release as **`v14.0.0`** (semver major). When merging to `main`, include **`#major`** in the merge (or squash) commit message.

## 13.0.0 - 2026-04-03

### Breaking changes

- **Laravel 12 and below** are no longer supported. This release targets **Laravel 13** only (`illuminate/contracts` ^13.0).
- **PHP** minimum is now **8.3** (aligned with Laravel 13: 8.3–8.5).
- **Saloon** is **v4** only (`saloonphp/saloon` ^4.0, `saloonphp/laravel-plugin` ^4.0). Saloon 3 is dropped.
- The Laravel facade alias was renamed from `Flatfox` to **`Zendesk`**. Update any `Flatfox::` references to `Zendesk::`.

### Changed

- Raised dev tooling for Laravel 13: `orchestra/testbench` ^11, `pestphp/pest` ^4, `larastan/larastan` ^3.9+, PHPUnit 12 (via Pest).
- Dependency refresh across `composer.json` / `composer.lock` (Guzzle, Spatie packages, PHPStan stack, etc.).
- **Security:** `phpunit.xml` no longer contains real Zendesk tokens or email; use environment or CI secrets if you add live API tests later.
- **Tests:** Unit coverage for `TicketType`, `TicketPriority`, `MalwareScanResult`, and the `Zendesk` class.

### Release tagging

- Ship this line as **`v13.0.0`** (semver major). When merging to `main`, include **`#major`** in the merge (or squash) commit message so the release workflow tags a major version instead of the default minor bump.
