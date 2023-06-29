# Temp Testing

```php
Route::get('/', function () {
    $connector = new ZendeskConnector();

    $response = $connector->send(new AllTicketsRequest());
    dump($response->dto());
    $response = $connector->send(new SingleTicketRequest(1));
    dump($response->dto());
    $response = $connector->send(new CountTicketsRequest());
    dump($response->dto());
    $response = $connector->send(
        new CreateSingleTicketRequest(
            CreateTicketDTO::fromArray([
                "comment" => CommentDTO::fromArray([
                    "body" => "The smoke is very colorful."
                ]),
                "priority" => "urgent",
                "subject" => "My printer is on fire!"
            ])
        )
    );
    dump($response->json());
});
```

<img src="https://banners.beyondco.de/Laravel%20Zendesk.png?theme=light&packageManager=composer+require&packageName=codebar-ag%2Flaravel-zendesk&pattern=circuitBoard&style=style_2&description=A+Laravel+Zendesk+integration.&md=1&showWatermark=1&fontSize=150px&images=home&widths=500&heights=500">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/codebar-ag/laravel-zendesk.svg?style=flat-square)](https://packagist.org/packages/codebar-ag/laravel-zendesk)
[![Total Downloads](https://img.shields.io/packagist/dt/codebar-ag/laravel-zendesk.svg?style=flat-square)](https://packagist.org/packages/codebar-ag/laravel-zendesk)
[![run-tests](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/run-tests.yml/badge.svg)](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/phpstan.yml/badge.svg)](https://github.com/codebar-ag/laravel-zendesk/actions/workflows/phpstan.yml)

This package was developed to give you a quick start to create tickets via the Zendesk API.

## 💡 What is Zendesk?

Zendesk is a cloud-based help desk management solution offering customizable tools to build customer service portal,
knowledge base and online communities.

## 🛠 Requirements

| Package 	 | PHP 	 | Laravel 	      | Zendesk 	 |
|-----------|-------|----------------|-----------|
| >v1.0     | >8.2  | > Laravel 10.0 | ✅         |

## Authentication

The currently supported authentication methods are:

| Method 	           | Supported 	 |
|--------------------|:-----------:|
| Basic Auth         |      ✅      |
| API token          |      ✅      |
| OAuth access token |      ❌      |

## ⚙️ Installation

You can install the package via composer:

```bash
composer require codebar-ag/laravel-zendesk
```

Optionally, you can publish the config file with:

```bash
php artisan vendor:publish --provider="CodebarAg\Zendesk\ZendeskServiceProvider" --tag="config"
```

You can add the following env variables to your `.env` file:

```dotenv
ZENDESK_SUBDOMAIN= # required
ZENDESK_AUTHENTICATION_METHOD= # basic or token, (token is default)
ZENDESK_EMAIL_ADDRESS= # required for both basic and token
ZENDESK_API_TOKEN= # required only for token authentication
ZENDESK_API_PASSWORD= # required only for basic authentication
```

## Usage

To make use of the package, you need to create a ZendeskConnector instance.

```php
use CodebarAg\Zendesk\ZendeskConnector;
...

$connector = new ZendeskConnector();

````

### Requests

The following requests are currently supported:

| Request 	         | Supported 	 |
|-------------------|:-----------:|
| List Tickets      |      ✅      |
| Count Tickets     |      ✅      |
| Show Ticket       |      ✅      |
| Create Ticket     |      ✅      |
| Create Attachment |      ✅      |

### Responses

The following responses are currently supported for retrieving the response body:

| Response Methods	 | Description                                                                                                                        | Supported 	 |
|----------------|------------------------------------------------------------------------------------------------------------------------------------|:-----------:|
| body()         | Returns the HTTP body as a string                                                                                                  |      ✅      |
| json()         | Retrieves a JSON response body and json_decodes it into an array.                                                                  |      ✅      |
| object()       | Retrieves a JSON response body and json_decodes it into an object.                                                                 |      ✅      |
| collect()      | Retrieves a JSON response body and json_decodes it into a Laravel collection. **Requires illuminate/collections to be installed.** |      ✅      |
| dto()          | Converts the response into a data-transfer object. You must define your DTO first                                                  |      ✅      |

See https://docs.saloon.dev/the-basics/responses for more information.

### Examples

#### Upload an attachment

```php
$upload = $connector->send(
    new CreateAttachmentRequest(
        fileName: 'head8.png',
        mimeType: Storage::disk('local')->mimeType('public/head8.png'),
        stream: Storage::disk('local')->readStream('public/head8.png')
    )
);

return $upload->json();
````

```json

```


```php

use CodebarAg\Zendesk\Requests\AllTicketsRequest;
use CodebarAg\Zendesk\Requests\CountTicketsRequest;
use CodebarAg\Zendesk\Requests\CreateAttachmentRequest;
use CodebarAg\Zendesk\Requests\CreateSingleTicketRequest;
use CodebarAg\Zendesk\Requests\SingleTicketRequest;
use CodebarAg\Zendesk\DTO\CommentDTO;
use CodebarAg\Zendesk\DTO\CreateTicketDTO;
use CodebarAg\Zendesk\Enums\TicketPriority;
use Illuminate\Support\Facades\Storage;
use CodebarAg\Zendesk\ZendeskConnector;

...

$connector = new ZendeskConnector();

$listTicketResponse = $connector->send(new AllTicketsRequest());
dump($listTicketResponse->dto());

$showTicketResponse = $connector->send(new SingleTicketRequest(1));
dump($showTicketResponse->dto());

$countTicketResponse = $connector->send(new SingleTicketRequest(1));
dump($countTicketResponse->dto());

$createTicketResponse = $connector->send(
    new CreateSingleTicketRequest(
        CreateTicketDTO::fromArray([
            "comment" => CommentDTO::fromArray([
                "body" => "The smoke is very colorful."
            ]),
            "priority" => TicketPriority::URGENT,
            "subject" => "My printer is on fire!"
        ])
    )
);

dump($createTicketResponse->dto());

$response = $connector->send(
    new CreateAttachmentRequest(
        fileName: 'head8.png',
        mimeType: Storage::disk('local')->mimeType('public/head8.png'),
        stream: Storage::disk('local')->readStream('public/head8.png')
    )
);

dump($response->dto());
```

## 🚧 Testing

Copy your own phpunit.xml-file.

```bash
cp phpunit.xml.dist phpunit.xml
```

Run the tests:

```bash
./vendor/bin/pest
```

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## ✏️ Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

```bash
composer test
```

### Code Style

```bash
./vendor/bin/pint
```

## 🧑‍💻 Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## 🙏 Credits

- [Sebastian Fix](https://github.com/StanBarrows)
- [All Contributors](../../contributors)
- [Skeleton Repository from Spatie](https://github.com/spatie/package-skeleton-laravel)
- [Laravel Package Training from Spatie](https://spatie.be/videos/laravel-package-training)

## 🎭 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
