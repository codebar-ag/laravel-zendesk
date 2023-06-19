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

## ⚙️ Installation

You can install the package via composer:

```bash
composer require codebar-ag/laravel-zendesk
```

## Usage

### Authentication

The currently supported authentication methods are:

| Method 	             | 	   |
|----------------------|-----|
| Basic authentication | No  |
| API token            | Yes |
| OAuth access token   | No  |

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
