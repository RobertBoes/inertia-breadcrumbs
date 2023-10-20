# Upgrading

This document outlines breaking changes introduced in 0.x versions and major releases.

We accept PRs to improve this guide.

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
