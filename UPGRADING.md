# Upgrading

## From v13 to v14

v14 replaces API token and basic authentication with **OAuth 2.0**. Zendesk is removing API tokens for the Support APIs:

| Date           | What happens                                    |
|----------------|-------------------------------------------------|
| 2026-10-27     | No new API tokens can be created                |
| **2027-04-30** | All existing API tokens are permanently disabled |

Source: [Announcing the removal of API tokens](https://support.zendesk.com/hc/en-us/articles/10851263566234)

### 1. Requirements

- PHP **8.5**
- Laravel **13**

### 2. Create an OAuth client in Zendesk

1. Go to **Admin Center → Apps and integrations → APIs → OAuth clients → Add OAuth client**.
2. Choose the client kind **Confidential**.
3. Copy the identifier (client ID) and the secret.
4. Only allow the scopes you need, e.g. `tickets:read tickets:write`.

### 3. Update your `.env`

```diff
  ZENDESK_SUBDOMAIN=your-subdomain
- ZENDESK_AUTHENTICATION_METHOD=token
- ZENDESK_EMAIL_ADDRESS=you@example.com
- ZENDESK_API_TOKEN=your-api-token
- ZENDESK_PASSWORD=your-password
+ ZENDESK_AUTHENTICATION_METHOD=oauth
+ ZENDESK_OAUTH_CLIENT_ID=your-client-identifier
+ ZENDESK_OAUTH_CLIENT_SECRET=your-client-secret
+ ZENDESK_OAUTH_SCOPES="read write"
```

If you published `config/zendesk.php`, delete it and publish it again:

```bash
php artisan vendor:publish --tag="zendesk-config" --force
php artisan config:clear
```

Access tokens are cached. Use a shared cache store (Redis, database, …) in production, or set `ZENDESK_CACHE_STORE`.

### 4. Code changes

| v13                                                    | v14                                                                   |
|--------------------------------------------------------|-----------------------------------------------------------------------|
| `$connector->setAuth()`                                | Removed                                                               |
| `catch (\Exception $e)` for config errors              | `catch (ZendeskConfigurationException $e)`                            |
| `$response->dto()` on a failed response throws `\Exception` | Throws Saloon `RequestException` (e.g. `NotFoundException`)       |
| `$dto->tickets[0]['subject']` (`AllTicketsDTO`)        | `$dto->tickets[0]->subject` (`SingleTicketDTO`)                       |
| `$attachment->width === '640'`                         | `$attachment->width === 640`                                          |
| `new CreateSingleTicketRequest(createTicket: ...)`     | `new CreateSingleTicketRequest(ticket: ...)`                          |
| Empty `created_at`/`due_at` became "now"               | They are `null`                                                       |

### 5. Tests in your app

The connector now requests an OAuth token before the first API call, so mock the token endpoint too:

```php
use CodebarAg\Zendesk\Requests\OAuth\CreateAccessTokenRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

MockClient::global([
    CreateAccessTokenRequest::class => MockResponse::make(['access_token' => 'fake', 'token_type' => 'bearer', 'expires_in' => 1800]),
    // ... your request mocks
]);
```

You can also skip OAuth in tests: set `ZENDESK_AUTHENTICATION_METHOD=access_token` and `ZENDESK_ACCESS_TOKEN=fake`.
