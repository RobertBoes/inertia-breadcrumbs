# Laravel package to automatically share breadcrumbs to Inertia

[![Latest Version on Packagist](https://img.shields.io/packagist/v/robertboes/inertia-breadcrumbs.svg?style=flat-square)](https://packagist.org/packages/robertboes/inertia-breadcrumbs)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/robertboes/inertia-breadcrumbs/run-tests?label=tests)](https://github.com/robertboes/inertia-breadcrumbs/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/robertboes/inertia-breadcrumbs/Check%20&%20fix%20styling?label=code%20style)](https://github.com/robertboes/inertia-breadcrumbs/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/robertboes/inertia-breadcrumbs.svg?style=flat-square)](https://packagist.org/packages/robertboes/inertia-breadcrumbs)


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

Next step is to install one of the following packages to manage your breadcrumbs:

- [diglactic/laravel-breadcrumbs](https://github.com/diglactic/laravel-breadcrumbs)
- [tabuna/breadcrumbs](https://github.com/tabuna/breadcrumbs)
- [glhd/gretel](https://github.com/glhd/gretel)

Configure your breadcrumbs as explained by the package

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
```

## Usage

No matter which third party package you're using, this package will always share breadcrumbs to Inertia in the following format:
```json
[
    {
        title: "Dashboard",
        url: "http://localhost/dashboard",
    },
    {
        title: "Profile",
        url: "http://localhost/dashboard/profile",
        current: true,
    }
]
```

An example to render your breadcrumbs in Vue 3 could look like the following:

```js
<template>
    <nav v-if="breadcrumbs">
        <ol>
            <li v-for="page in breadcrumbs">
                <div>
                    <span v-if="page ==='/'">/</span>
                    <a
                        v-else
                        :href="page.url"
                        :class="{ 'border-b border-blue-400': page.current }"
                    >{{ page.title }}</a>
                </div>
            </li>
        </ol>
    </nav>
</template>

<script>
import { usePage } from '@inertiajs/inertia-vue3'
import { computed } from 'vue'

export default {
    setup() {
        // Insert an element between all elements, insertBetween([1, 2, 3], '/') results in [1, '/', 2, '/', 3]
        const insertBetween = (items, insertion) => {
            return items.flatMap(
                (value, index, array) =>
                    array.length - 1 !== index
                        ? [value, insertion]
                        : value,
            )
        }

        const breadcrumbs = computed(() => insertBetween(usePage().props.value.breadcrumbs || [], '/'))

        return {
            breadcrumbs,
        }
    },
}
</script>
```

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

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## TODO
- [ ] Create Vue 2/3 components

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Robert Boes](https://github.com/RobertBoes)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
