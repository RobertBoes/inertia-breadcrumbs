# Laravel package to automatically share breadcrumbs to Inertia

[![Latest Version on Packagist](https://img.shields.io/packagist/v/robertboes/inertia-breadcrumbs.svg?style=flat-square)](https://packagist.org/packages/robertboes/inertia-breadcrumbs)
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/robertboes/inertia-breadcrumbs?style=flat-square)](https://packagist.org/packages/robertboes/inertia-breadcrumbs)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/RobertBoes/inertia-breadcrumbs/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/robertboes/inertia-breadcrumbs/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/RobertBoes/inertia-breadcrumbs/pint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/RobertBoes/inertia-breadcrumbs/actions?query=workflow%3A%22Check+%26+fix+styling%22+branch%3Amain++)
[![GitHub tag (latest SemVer)](https://img.shields.io/github/v/tag/RobertBoes/inertia-breadcrumbs?label=latest%20version&style=flat-square)](https://github.com/RobertBoes/inertia-breadcrumbs/releases/latest)
[![Total Downloads](https://img.shields.io/packagist/dt/robertboes/inertia-breadcrumbs.svg?style=flat-square)](https://packagist.org/packages/robertboes/inertia-breadcrumbs)
[![GitHub](https://img.shields.io/github/license/RobertBoes/inertia-breadcrumbs?style=flat-square)](https://github.com/RobertBoes/inertia-breadcrumbs/blob/main/LICENSE.md)


This package automatically shares breadcrumbs as Inertia props in a standardized way, with support for multiple breadcrumb packages. 

## Installation

You can install the package via composer:

```bash
composer require robertboes/inertia-breadcrumbs
```

You can publish the config file with:
```bash
php artisan vendor:publish --tag="inertia-breadcrumbs-config"
```

Next step is to install one of the following packages to manage your breadcrumbs, or use the built-in closure collector:

- [diglactic/laravel-breadcrumbs](https://github.com/diglactic/laravel-breadcrumbs)
- [tabuna/breadcrumbs](https://github.com/tabuna/breadcrumbs)
- [glhd/gretel](https://github.com/glhd/gretel)
- Built-in closure collector (no additional package required)

Configure your breadcrumbs as explained by the package you've chosen.

Update your `config/inertia-breadcrumbs.php` configuration to use the correct collector:
```php
// diglactic/laravel-breadcrumbs
use RobertBoes\InertiaBreadcrumbs\Collectors\DiglacticBreadcrumbsCollector;

return [
    'collector' => DiglacticBreadcrumbsCollector::class,
];

// tabuna/breadcrumbs
use RobertBoes\InertiaBreadcrumbs\Collectors\TabunaBreadcrumbsCollector;

return [
    'collector' => TabunaBreadcrumbsCollector::class,
];

// glhd/gretel
use RobertBoes\InertiaBreadcrumbs\Collectors\GretelBreadcrumbsCollector;

return [
    'collector' => GretelBreadcrumbsCollector::class,
];

// Built-in closure collector
use RobertBoes\InertiaBreadcrumbs\Collectors\ClosureBreadcrumbsCollector;

return [
    'collector' => ClosureBreadcrumbsCollector::class,
];
```

## Usage

No matter which third party package you're using, this package will always share breadcrumbs to Inertia in the following format:
```json
[
    {
        "title": "Dashboard",
        "url": "http://localhost/dashboard"
    },
    {
        "title": "Profile",
        "url": "http://localhost/dashboard/profile",
        "current": true
    },
    {
        "title": "Breadcrumb without URL"
    }
]
```

> [!NOTE]
> Note that due to package differences, URLs are always present when using `glhd/gretel`, but are otherwise optional.  

An example to render your breadcrumbs in Vue 3 could look like the following:

```vue
<template>
    <nav v-if="$page.props.breadcrumbs">
        <ol>
            <li v-for="crumb in $page.props.breadcrumbs" :key="crumb.title">
                <a
                    v-if="crumb.url"
                    :href="crumb.url"
                    :class="{ 'border-b border-blue-400': crumb.current }"
                >{{ crumb.title }}</a>
                <span v-else>{{ crumb.title }}</span>
            </li>
        </ol>
    </nav>
</template>
```

## Using the closure collector

If you don't want to install a third-party breadcrumb package, you can use the built-in closure collector. This allows you to define breadcrumbs directly using closures, either in a service provider or in your controllers.

Update your `config/inertia-breadcrumbs.php` to use the `ClosureBreadcrumbsCollector`:
```php
use RobertBoes\InertiaBreadcrumbs\Collectors\ClosureBreadcrumbsCollector;

return [
    'collector' => ClosureBreadcrumbsCollector::class,
];
```

Then define your breadcrumbs by route name. You can do this in a service provider:
```php
<?php

namespace App\Providers;

use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $breadcrumbs = app(InertiaBreadcrumbs::class);

        $breadcrumbs->for('users.index', fn () => [
            Breadcrumb::make('Users', route('users.index')),
        ]);

        $breadcrumbs->for('users.show', fn (User $user) => [
            Breadcrumb::make('Users', route('users.index')),
            Breadcrumb::make($user->name, route('users.show', $user)),
        ]);
    }
}
```

Or directly in your controllers. When called without a route name, the current route name is automatically inferred from the request:
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;

class UserController extends Controller
{
    public function show(User $user, InertiaBreadcrumbs $breadcrumbs)
    {
        $breadcrumbs->for(fn (User $user) => [
            Breadcrumb::make('Users', route('users.index')),
            Breadcrumb::make($user->name, route('users.show', $user)),
        ]);

        return inertia('Users/Show', ['user' => $user]);
    }
}
```

Route parameters are automatically passed to the closure based on the current route. The `current` property is automatically determined by comparing the breadcrumb URL with the current request URL, but you can also set it explicitly via `Breadcrumb::make('Title', $url, current: true)`.

> [!NOTE]
> When using the shorthand (passing a closure as the first argument) on a named route, the route name is automatically inferred from the request. On unnamed routes, the breadcrumbs are stored as pending and resolved for the current request only. The service provider approach always requires an explicit route name.

## Share strategy

Controls how breadcrumbs are shared with Inertia. You can configure this in `config/inertia-breadcrumbs.php`:

```php
use RobertBoes\InertiaBreadcrumbs\ShareStrategy;

return [
    'share' => ShareStrategy::Default,
];
```

- `ShareStrategy::Default` — Standard shared prop, excluded during partial reloads unless explicitly requested
- `ShareStrategy::Always` — Always included in the response, even during partial reloads
- `ShareStrategy::Deferred` — Excluded from the initial page load, automatically fetched after the page renders

You can also use string values (`'default'`, `'always'`, `'deferred'`) instead of the enum.

## Using a classifier

A classifier is used to determine when breadcrumbs should be shared as Inertia props.
By default all breadcrumbs are shared, but this package is shipped with the `IgnoreSingleBreadcrumbs` classifier, which simply discards a breadcrumb collection containing only one route.

To write your own classifier you'll have to implement `RobertBoes\InertiaBreadcrumbs\Classifier\ClassifierContract` and update the `inertia-breadcrumbs.classifier` config, for example:

```php
<?php

namespace App\Support;

use Illuminate\Support\Str;
use RobertBoes\InertiaBreadcrumbs\Classifier\ClassifierContract;
use RobertBoes\InertiaBreadcrumbs\BreadcrumbCollection;

class IgnoreAdminBreadcrumbs implements ClassifierContract
{
    public function shouldShareBreadcrumbs(BreadcrumbCollection $collection): bool
    {
        return ! Str::startsWith($collection->first()?->url(), '/admin');
    }
}
```

## Serializing breadcrumbs

In some cases you might not like the default way breadcrumbs are serialized.
To modify the way the breadcrumbs are being sent to the frontend you can register a serialize callback
in the `boot` method of a service provider:

```php
<?php

namespace App\Providers;

use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        app(InertiaBreadcrumbs::class)->serializeUsing(fn (Breadcrumb $breadcrumb) => [
            'name' => $breadcrumb->title(),
            'href' => $breadcrumb->url(),
            'active' => $breadcrumb->current(),
            'data' => $breadcrumb->data(),
        ]);
    }
}
```

## Including the query string when determining the current URL

By default, the query string will be ignored when determining the current url, meaning a breadcrumb defined for `/users/{id}` will match both `/users/1` and `/users/1?foo=bar`. To change this behaviour and include the query string (meaning `/users/1?foo=bar` will not be seen as the current page), change `ignore_query` to `false` in the `config/inertia-breadcrumbs.php` file.

### Notes on using `glhd/gretel`

`glhd/gretel` shares the breadcrumbs automatically if it detects Inertia is installed and shares the props with the same key (`breadcrumbs`). If you want to use this package with gretel you should disable their automatic sharing by updating the config:

```php
// config/gretel.php

return [
    'packages' => [
        'inertiajs/inertia-laravel' => false,
    ],
];
```

## Testing

```bash
composer test
```

## Upgrading

For notable changes see [UPGRADING](UPGRADING.md).

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
