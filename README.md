# Laravel package to automatically generates breadcrumbs for your Inertia app

[![Latest Version on Packagist](https://img.shields.io/packagist/v/robertboes/inertia-breadcrumbs.svg?style=flat-square)](https://packagist.org/packages/robertboes/inertia-breadcrumbs)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/robertboes/inertia-breadcrumbs/run-tests?label=tests)](https://github.com/robertboes/inertia-breadcrumbs/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/robertboes/inertia-breadcrumbs/Check%20&%20fix%20styling?label=code%20style)](https://github.com/robertboes/inertia-breadcrumbs/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/robertboes/inertia-breadcrumbs.svg?style=flat-square)](https://packagist.org/packages/robertboes/inertia-breadcrumbs)


This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require robertboes/inertia-breadcrumbs
```

You can publish the config file with:
```bash
php artisan vendor:publish --tag="inertia-breadcrumbs_without_prefix-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$inertia-breadcrumbs = new RobertBoes\InertiaBreadcrumbs();
echo $inertia-breadcrumbs->echoPhrase('Hello, RobertBoes!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Robert Boes](https://github.com/RobertBoes)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
