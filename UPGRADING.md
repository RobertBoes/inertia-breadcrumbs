# Upgrading

This document outlines breaking changes introduced in 0.x versions and major releases.

We accept PRs to improve this guide.

## From 0.8.x to 1.0.0

### Minimum version requirements

This release drops support for older versions of PHP, Laravel, and Inertia:

- PHP 8.2 or higher is now required (was 8.1)
- Laravel 12 or higher is now required (was 10)
- Inertia Laravel 2.0 or higher is now required (was 1.0)

### Breadcrumbs prop is always present

Previously, the `breadcrumbs` prop was only shared with Inertia when breadcrumbs were defined for the current route.
Starting with 1.0, the `breadcrumbs` prop is always present in the Inertia response. When no breadcrumbs are defined for the current route, the value will be `null`.

If your frontend checks for the existence of the `breadcrumbs` prop, you should update it to check for a `null` value instead:

```diff
- <nav v-if="'breadcrumbs' in $page.props">
+ <nav v-if="$page.props.breadcrumbs">
```

### Custom serialization API change

`InertiaBreadcrumbs` is no longer a static class. It's now registered as a singleton in the container.
If you're using `serializeUsing`, update your code:

```diff
- use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;
-
- InertiaBreadcrumbs::serializeUsing(fn (Breadcrumb $breadcrumb) => [...]);
+ use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
+ use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;
+
+ app(InertiaBreadcrumbs::class)->serializeUsing(fn (Breadcrumb $breadcrumb) => [...]);
```

### Share strategy configuration

A new `share` configuration option has been added to control how breadcrumbs are shared with Inertia.
It's recommended to add this to your config:

```diff
return [
    'middleware' => [
        // ...
    ],

+    /**
+     * Controls how breadcrumbs are shared with Inertia.
+     */
+    'share' => \RobertBoes\InertiaBreadcrumbs\ShareStrategy::Default,
+
    'collector' => DiglacticBreadcrumbsCollector::class,
    // ...
];
```

See the [README](README.md#share-strategy) for the available strategies.

### New built-in closure collector

If you don't want to use a third-party breadcrumb package, you can now use the built-in `ClosureBreadcrumbsCollector`.
This allows you to define breadcrumbs directly using closures, without installing any additional packages.
See the [README](README.md#using-the-closure-collector) for usage details.

## From 0.5.x to 0.6.0

### Breadcrumbs without a route

`null` breadcrumb URLs are now supported for `diglactic/laravel-breadcrumbs` and `tabuna/breadcrumbs` collectors. 
Previously, defining a `null` URL would've thrown an exception, so this is technically a backwards-compatible change.
However, because this changes the array shape of outputted breadcrumbs, you may want to update your frontend components
to match. Here's an example TypeScript type to illustrate:

```diff
type Breadcrumb {
    title: string;
-   url: string;
+   url?: string;
    current?: boolean;
};

type Breadcrumbs = Breadcrumb[];
```

## From 0.4.x to 0.5.x

### Upgrading dependencies

This release adds support for Laravel 11. This package now also requires `inertiajs/inertia-laravel: ^1.0`.

### Custom shared key

@squiaios added the ability to change the prop key used to share the breadcrumbs.
By default these are shared using the `breadcrumbs` key.
It's recommended to keep your config up-to-date with this change:

```diff
return [
    'middleware' => [
        // ...
+
+        /**
+         * The key of shared breadcrumbs
+         */
+        'key' => 'breadcrumbs',
    ],
];
```

## From 0.3.x to 0.4.x

### Query string is now ignored when determining the current URL (#9)

In version <0.3.x an active breadcrumb was determined by the full URL.
This meant that a route such as `/users/{id}` would not set the active breadcrumb when a user visits `/users/1?foo=bar`.
The default behaviour has been changed to ignore the query string.

The config file has been updated to disable this behaviour:
```diff
return [
    // ...

+    /**
+     * Whether the query string should be ignored when determining the current route
+     */
+    'ignore_query' => true,
];
```

If your app relies on the previous behaviour (not ignoring the query string) you can set the `inertia-breadcrumbs.ignore_query` config value to `false`.
