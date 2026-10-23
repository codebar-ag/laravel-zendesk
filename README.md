<img src="https://banners.beyondco.de/Laravel%20Zendesk.png?theme=light&packageManager=composer+require&packageName=codebar-ag%2Flaravel-zendesk&pattern=circuitBoard&style=style_2&description=A+Laravel+Zendesk+integration.&md=1&showWatermark=0&fontSize=150px&images=home&widths=500&heights=500">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/codebar-ag/laravel-zendesk.svg?style=flat-square)](https://packagist.org/packages/codebar-ag/laravel-zendesk)
[![Total Downloads](https://img.shields.io/packagist/dt/codebar-ag/laravel-zendesk.svg?style=flat-square)](https://packagist.org/packages/codebar-ag/laravel-zendesk)
[![GitHub-Tests](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/run-tests.yml)
[![GitHub Code Style](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/fix-php-code-style-issues.yml/badge.svg?branch=main)](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/fix-php-code-style-issues.yml)
[![PHPStan](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/phpstan.yml/badge.svg)](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/phpstan.yml)
[![Dependency Review](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/dependency-review.yml/badge.svg)](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/dependency-review.yml)

This package gives you a quick start for working with the Zendesk Support API (tickets and attachments) from Laravel. It is built on [Saloon](https://docs.saloon.dev) and authenticates with **OAuth 2.0**.

> [!IMPORTANT]
> Zendesk is [removing API tokens](https://support.zendesk.com/hc/en-us/articles/10851263566234) for the Support APIs. No new API tokens can be created after **October 27, 2026**, and all existing tokens stop working on **April 30, 2027**. Starting with **v14**, this package only supports OAuth. See [UPGRADING.md](UPGRADING.md) for how to migrate.

## 💡 What is Zendesk?

Zendesk is a cloud-based help desk management solution offering customizable tools to build customer service portals,
knowledge base and online communities.

## 🛠 Requirements

| Package 	 | PHP 	       | Laravel 	 | Authentication          |
|-----------|-------------|-----------|-------------------------|
| v14.0     | 8.5         | 13.x      | OAuth 2.0               |
| v13.0     | ^8.3 - ^8.5 | 13.x      | Basic, API token        |
| v12.0     | ^8.2 - ^8.4 | 12.x      | Basic, API token        |
| v11.0     | ^8.2 - ^8.3 | 11.x      | Basic, API token        |
| v1.0      | ^8.2        | 10.x      | Basic, API token        |

## 🔐 Authentication

| Method                                      | Config value   | Supported |
|---------------------------------------------|----------------|:---------:|
| OAuth client credentials (server to server) | `oauth`        |     ✅     |
| OAuth access token (managed by your app)    | `access_token` |     ✅     |
| API token                                   | –              |  ❌ (v13)  |
| Basic Auth (email + password)               | –              |  ❌ (v13)  |

### Create an OAuth client in Zendesk

1. In Zendesk **Admin Center**, go to **Apps and integrations → APIs → OAuth clients** and click **Add OAuth client**.
2. Choose the client kind **Confidential**. The client credentials grant does not work with public clients.
3. Copy the **Identifier** (client ID) and the **Secret**. Zendesk shows the secret only once.
4. Only allow the scopes your app needs, e.g. `tickets:read tickets:write`.

More details: [Using OAuth authentication with your application](https://support.zendesk.com/hc/en-us/articles/4408845965210).

## ⚙️ Installation

You can install the package via composer:

```bash
composer require codebar-ag/laravel-zendesk
```

Optionally, you can publish the config file with:

```bash
php artisan vendor:publish --provider="CodebarAg\Zendesk\ZendeskServiceProvider" --tag="zendesk-config"
```

Add the following variables to your `.env` file:

```dotenv
ZENDESK_SUBDOMAIN=your-subdomain                     # required, e.g. "acme" for https://acme.zendesk.com
ZENDESK_AUTHENTICATION_METHOD=oauth                  # default, 'oauth' or 'access_token'
ZENDESK_OAUTH_CLIENT_ID=your-oauth-client-identifier # required for 'oauth'
ZENDESK_OAUTH_CLIENT_SECRET=your-oauth-client-secret # required for 'oauth'
ZENDESK_OAUTH_SCOPES="read write"                    # optional, space separated, default "read write"
ZENDESK_OAUTH_EXPIRES_IN=                            # optional, token lifetime in seconds (300 - 172800)
ZENDESK_CACHE_STORE=                                 # optional, cache store for access tokens, default store if empty
```

### How OAuth works in this package

With the `oauth` method, the connector uses the OAuth **client credentials** grant (`POST https://{subdomain}.zendesk.com/oauth/tokens`):

- Before the first request, the package requests an access token and sends it as `Authorization: Bearer …`.
- The token is cached in your Laravel cache and renewed automatically 60 seconds before it expires. New Zendesk OAuth clients issue tokens that are valid for 30 minutes by default.
- A cache lock makes sure that parallel workers request only one token at a time.
- If Zendesk rejects a cached token with `401`, the token is discarded and the request is retried **once** with a fresh token.

> [!TIP]
> Use a shared cache store (Redis, database, …) in production. With the `array` store, every PHP process requests its own token.

The automatic retry applies to `$connector->send()`. Requests sent with `sendAsync()` or `pool()` still use the cached token, but they are not retried.

### Using your own access token

If your app already has an OAuth access token, for example from an authorization code flow, use the `access_token` method:

```dotenv
ZENDESK_AUTHENTICATION_METHOD=access_token
ZENDESK_ACCESS_TOKEN=your-access-token
```

or set the token at runtime:

```php
use Saloon\Http\Auth\TokenAuthenticator;

$connector = new ZendeskConnector();
$connector->authenticate(new TokenAuthenticator($user->zendesk_access_token));
```

The package does not refresh tokens that you pass in this way.

## Usage

Create a connector, either directly or through the facade:

```php
use CodebarAg\Zendesk\Facades\Zendesk;
use CodebarAg\Zendesk\ZendeskConnector;

$connector = new ZendeskConnector();
// or
$connector = Zendesk::connector();
```

### Multiple Zendesk accounts

Pass the subdomain and the OAuth credentials to connect to another account at runtime. Each combination of subdomain, client and scopes gets its own cached token.

```php
use CodebarAg\Zendesk\Auth\OAuthCredentials;
use CodebarAg\Zendesk\ZendeskConnector;

$connector = new ZendeskConnector(
    subdomain: 'acme',
    credentials: new OAuthCredentials(
        clientId: 'acme-client',
        clientSecret: 'acme-secret',
        scopes: ['tickets:read', 'tickets:write'],
    ),
);
```

### Error handling

| Situation                                                    | Exception                                                   |
|--------------------------------------------------------------|-------------------------------------------------------------|
| Missing or invalid config (subdomain, client ID, method, …)  | `CodebarAg\Zendesk\Exceptions\ZendeskConfigurationException`  |
| Zendesk refuses to issue an access token                     | `CodebarAg\Zendesk\Exceptions\ZendeskAuthenticationException` |
| Calling `->dto()` on a failed response (4xx/5xx)             | Saloon `RequestException`, e.g. `NotFoundException`          |

Both package exceptions extend `CodebarAg\Zendesk\Exceptions\ZendeskException`. `send()` itself does not throw for failed responses. Check `$response->failed()`, or call `$response->throw()`.

### Requests

The following requests are currently supported:

| Request            | PHP class                    | Supported |
|--------------------|------------------------------|:---------:|
| List tickets       | `AllTicketsRequest`          |     ✅     |
| Count tickets      | `CountTicketsRequest`        |     ✅     |
| Get ticket         | `SingleTicketRequest`        |     ✅     |
| Create ticket      | `CreateSingleTicketRequest`  |     ✅     |
| Create attachment  | `CreateAttachmentRequest`    |     ✅     |

### Responses

The following responses are currently supported for retrieving the response body:

| Response Methods | Description                                                                                                                        | Supported |
|------------------|------------------------------------------------------------------------------------------------------------------------------------|:---------:|
| body             | Returns the HTTP body as a string                                                                                                  |     ✅     |
| json             | Retrieves a JSON response body and json_decodes it into an array.                                                                  |     ✅     |
| object           | Retrieves a JSON response body and json_decodes it into an object.                                                                 |     ✅     |
| collect          | Retrieves a JSON response body and json_decodes it into a Laravel collection. **Requires illuminate/collections to be installed.** |     ✅     |
| dto              | Converts the response into a DTO. This package provides DTOs for the requests above; Saloon also allows custom DTOs.                 |     ✅     |

See https://docs.saloon.dev/the-basics/responses for more information.

### Enums

We provide enums for the following values:

| Enum 	            |                               Values 	                                |
|-------------------|:---------------------------------------------------------------------:|
| TicketPriority    |                   'urgent', 'high', 'normal', 'low'                   |
| TicketType        |               'incident', 'problem', 'question', 'task'               |
| MalwareScanResult | 'malware_found', 'malware_not_found', 'failed_to_scan', 'not_scanned' |

`Note: When using the dto method on a response, the enum values will be converted to their respective enum class.`

### Zendesk API and DTO coverage

Official ticket JSON is documented in the [Zendesk Tickets API](https://developer.zendesk.com/api-reference/ticketing/tickets/tickets/). [`SingleTicketDTO`](src/Dto/Tickets/SingleTicketDTO.php) maps a large subset of the fields Zendesk returns, but it may not include every property. Missing dates are `null`. They are not replaced with the current time.

### DTOs

We provide DTOs for the following:

| DTO               | PHP class |
|-------------------|-----------|
| AttachmentDTO     | `CodebarAg\Zendesk\Dto\Tickets\Attachments\AttachmentDTO` |
| ThumbnailDTO      | `CodebarAg\Zendesk\Dto\Tickets\Attachments\ThumbnailDTO` |
| UploadDTO         | `CodebarAg\Zendesk\Dto\Tickets\Attachments\UploadDTO` |
| CommentDTO        | `CodebarAg\Zendesk\Dto\Tickets\Comments\CommentDTO` |
| AllTicketsDTO     | `CodebarAg\Zendesk\Dto\Tickets\AllTicketsDTO` |
| CountTicketsDTO   | `CodebarAg\Zendesk\Dto\Tickets\CountTicketsDTO` |
| SingleTicketDTO   | `CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO` |
| AccessTokenDTO    | `CodebarAg\Zendesk\Dto\OAuth\AccessTokenDTO` |

`Note: This is the preferred way to work with requests and responses. You can still use the json, object, and collect methods, and pass arrays into requests where supported.`

### Examples

#### Create a ticket

```php
use CodebarAg\Zendesk\Requests\CreateSingleTicketRequest;
use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use CodebarAg\Zendesk\Dto\Tickets\Comments\CommentDTO;
use CodebarAg\Zendesk\Enums\TicketPriority;
...

$ticketResponse = $connector->send(
    new CreateSingleTicketRequest(
        SingleTicketDTO::fromArray([
            'comment' => CommentDTO::fromArray([
                'body' => 'The smoke is very colorful.',
            ]),
            'priority' => TicketPriority::URGENT,
            "subject" => "My printer is on fire!",
            "custom_fields" => [
                [
                    "id" => 12345678910111,
                    "value" => "Your custom field value"
                ],
                [
                    "id" => 12345678910112,
                    "value" => "Your custom field value 2"
                ],
            ],
        ])
    )
);

$ticket = $ticketResponse->dto();
```

#### List all tickets

```php
use CodebarAg\Zendesk\Requests\AllTicketsRequest;
...

$listTicketResponse = $connector->send(new AllTicketsRequest());
$tickets = $listTicketResponse->dto()->tickets; // array of SingleTicketDTO
```

#### Count all tickets

```php
use CodebarAg\Zendesk\Requests\CountTicketsRequest;
...

$countTicketResponse = $connector->send(new CountTicketsRequest());
$countTicketResponse->dto();
```

#### Get a ticket

```php
use CodebarAg\Zendesk\Requests\SingleTicketRequest;
...

$ticketId = 1;

$ticketResponse = $connector->send(new SingleTicketRequest($ticketId));
$ticketResponse->dto();
```

#### Upload an attachment

```php
use CodebarAg\Zendesk\Requests\CreateAttachmentRequest;
use CodebarAg\Zendesk\Requests\CreateSingleTicketRequest;
use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use CodebarAg\Zendesk\Dto\Tickets\Comments\CommentDTO;
use Illuminate\Support\Facades\Storage;

$uploadResponse = $connector->send(
    new CreateAttachmentRequest(
        fileName: 'someimage.png',
        mimeType: Storage::disk('local')->mimeType('public/someimage.png'),
        stream: Storage::disk('local')->readStream('public/someimage.png')
    )
);

$token = $uploadResponse->dto()->token;

// Optional: add more files to the same upload
$connector->send(new CreateAttachmentRequest(
    fileName: 'second.pdf',
    mimeType: 'application/pdf',
    stream: Storage::disk('local')->readStream('public/second.pdf'),
    token: $token,
));

$ticketResponse = $connector->send(
    new CreateSingleTicketRequest(
        SingleTicketDTO::fromArray([
            'comment' => CommentDTO::fromArray([
                ...
                'uploads' => [
                    $token,
                ],
            ]),
        ])
    )
);

$ticket = $ticketResponse->dto();
```

## 🚧 Testing

The test suite never calls the real Zendesk API on a normal run. Stray requests are blocked, and every response is mocked.

```bash
cp phpunit.xml.dist phpunit.xml
composer test
composer analyse
```

### Live tests with recorded fixtures

The tests in `tests/Live` run against **real Zendesk responses** that were recorded with Saloon fixtures (`tests/Fixtures/Saloon`). On a normal run they replay those fixtures offline. If a fixture is missing, its test is skipped.

> [!NOTE]
> The fixtures in this repository are currently **hand-written placeholders** based on the Zendesk API reference. They are marked with `"synthetic": true` in their `context`. Until they are recorded against a real Zendesk account, the test `replays fixtures recorded from the live Zendesk API` is skipped as a reminder.

To record or refresh the fixtures against your Zendesk account:

1. Create a confidential OAuth client with `read write` scope. Use a **sandbox or test account**.
2. Put the credentials in your local, git-ignored `phpunit.xml`:
   ```xml
   <env name="ZENDESK_SUBDOMAIN" value="your-subdomain"/>
   <env name="ZENDESK_OAUTH_CLIENT_ID" value="your-client-id"/>
   <env name="ZENDESK_OAUTH_CLIENT_SECRET" value="your-client-secret"/>
   ```
3. Run:
   ```bash
   composer test:record
   ```

Record mode sends every live test request to Zendesk and overwrites the fixtures. Before saving, [`ZendeskFixture`](tests/Fixtures/ZendeskFixture.php) redacts:
- access and refresh tokens
- email addresses, names and phone numbers
- cookies and tracing headers
- your subdomain, which is replaced with `codebar-zendesk`

Review the diff before committing the fixtures. Recording creates test tickets tagged `laravel-zendesk-test`, which you can bulk-delete in Zendesk.

### Testing your own app

Fake the token endpoint together with your requests:

```php
use CodebarAg\Zendesk\Requests\CountTicketsRequest;
use CodebarAg\Zendesk\Requests\OAuth\CreateAccessTokenRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

MockClient::global([
    CreateAccessTokenRequest::class => MockResponse::make(['access_token' => 'fake', 'token_type' => 'bearer', 'expires_in' => 1800]),
    CountTicketsRequest::class => MockResponse::make(['count' => ['value' => 1, 'refreshed_at' => now()->toIso8601String()]]),
]);
```

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## ✏️ Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

```bash
composer test
```

### Code Style

```bash
composer format
```

## 🧑‍💻 Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on reporting security vulnerabilities.

## 🙏 Credits
- [Sebastian Bürgin-Fix](https://github.com/StanBarrows)
- [All Contributors](../../contributors)
- [Skeleton Repository from Spatie](https://github.com/spatie/package-skeleton-laravel)
- [Laravel Package Training from Spatie](https://spatie.be/videos/laravel-package-training)
- [Laravel Saloon by Sam Carré](https://github.com/Sammyjo20/Saloon)

## 🎭 License

The MIT License (MIT). Please have a look at [License File](LICENSE.md) for more information.
