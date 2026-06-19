# Configuring & extending

All options live in `config/inertia-breadcrumbs.php` (publish with `php artisan vendor:publish --tag="inertia-breadcrumbs-config"`), except the serializer which is registered in code.

## Config keys

```php
return [
    'middleware' => [
        'enabled' => true,   // set false to register the middleware yourself
        'group'   => 'web',  // middleware group the breadcrumb sharing is added to
        'key'     => 'breadcrumbs', // Inertia prop key
    ],

    'share' => \RobertBoes\InertiaBreadcrumbs\ShareStrategy::Default,

    'collector' => \RobertBoes\InertiaBreadcrumbs\Collectors\DiglacticBreadcrumbsCollector::class,

    'classifier' => \RobertBoes\InertiaBreadcrumbs\Classifier\AppendAllBreadcrumbs::class,

    'ignore_query' => true, // ignore the query string when matching the current URL
];
```

- Changing `middleware.key` changes the prop name on the frontend (default `breadcrumbs`).
- `ignore_query = true` means `/users/1` and `/users/1?foo=bar` both match the breadcrumb for `/users/{id}`. Set it to `false` to treat query strings as distinct.

## Share strategy

Controls how the prop is shared with Inertia. Accepts the `ShareStrategy` enum or the equivalent string (`'default'`, `'always'`, `'deferred'`):

- `ShareStrategy::Default` — standard shared prop; excluded from partial reloads unless explicitly requested.
- `ShareStrategy::Always` — always included, even during partial reloads.
- `ShareStrategy::Deferred` — excluded from the initial load, fetched automatically after the page renders.

```php
use RobertBoes\InertiaBreadcrumbs\ShareStrategy;

'share' => ShareStrategy::Deferred, // or 'deferred'
```

## Classifier — decide when breadcrumbs are shared

A classifier implements `RobertBoes\InertiaBreadcrumbs\Classifier\ClassifierContract` and decides whether a collection is shared. Built-in:

- `AppendAllBreadcrumbs` (default) — always share.
- `IgnoreSingleBreadcrumbs` — drop a collection that contains only one breadcrumb.

Custom classifier:

```php
namespace App\Support;

use Illuminate\Support\Str;
use RobertBoes\InertiaBreadcrumbs\BreadcrumbCollection;
use RobertBoes\InertiaBreadcrumbs\Classifier\ClassifierContract;

class IgnoreAdminBreadcrumbs implements ClassifierContract
{
    public function shouldShareBreadcrumbs(BreadcrumbCollection $collection): bool
    {
        // URLs are absolute; compare against an absolute URL, and handle a null URL.
        return ! Str::startsWith($collection->first()?->url() ?? '', url('/admin'));
    }
}
```

Then set `'classifier' => \App\Support\IgnoreAdminBreadcrumbs::class`.

## Custom serialization

To change the per-breadcrumb output shape, register a callback in a service provider's `boot()` (this overrides the default `title`/`url`/`current`/`data` keys):

```php
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;

app(InertiaBreadcrumbs::class)->serializeUsing(fn (Breadcrumb $breadcrumb) => [
    'name'   => $breadcrumb->title(),
    'href'   => $breadcrumb->url(),
    'active' => $breadcrumb->current(),
    'data'   => $breadcrumb->data(),
]);
```

When a serializer is registered, the frontend prop uses *these* keys — update render components accordingly.

## Using `glhd/gretel` — disable its own Inertia sharing

Gretel auto-shares its own `breadcrumbs` prop when it detects Inertia, which collides with this package. Disable it in `config/gretel.php`:

```php
return [
    'packages' => [
        'inertiajs/inertia-laravel' => false,
    ],
];
```

## Registering the middleware yourself

Set `middleware.enabled` to `false`, then add `\RobertBoes\InertiaBreadcrumbs\Middleware::class` to whichever group/stack you want. Useful when breadcrumbs should only be shared on specific route groups.
