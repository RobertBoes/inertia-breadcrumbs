---
name: inertia-breadcrumbs
description: Define, render, and configure breadcrumbs in a Laravel + Inertia app using the robertboes/inertia-breadcrumbs package. Use whenever adding or editing breadcrumbs for a page, building a breadcrumb trail or nav in an Inertia page component (Vue, React, or Svelte), choosing or configuring the breadcrumb collector (diglactic, tabuna, gretel, or the built-in closure collector), or customizing the share strategy, classifier, or serialization. Not for breadcrumbs outside an Inertia response.
---

# Inertia Breadcrumbs

`robertboes/inertia-breadcrumbs` collects breadcrumbs from one of several sources and shares them to the frontend as an Inertia prop (default key: `breadcrumbs`). It does **not** define breadcrumbs itself for the third-party collectors — it adapts whatever the configured source produces into one consistent prop shape.

The package supports four breadcrumb sources and any Inertia frontend, so the correct guidance depends entirely on how *this* project is set up. Detect that first; never assume a default.

## Step 1 — Detect the setup (do not assume)

Before writing or editing anything, determine:

1. **Which collector is configured** — read `config/inertia-breadcrumbs.php` and look at the `collector` key. It will be one of:
   - `ClosureBreadcrumbsCollector` — breadcrumbs defined in PHP via this package's own API (no extra dependency).
   - `DiglacticBreadcrumbsCollector` — breadcrumbs defined with `diglactic/laravel-breadcrumbs` (usually `routes/breadcrumbs.php`).
   - `TabunaBreadcrumbsCollector` — breadcrumbs defined with `tabuna/breadcrumbs`.
   - `GretelBreadcrumbsCollector` — breadcrumbs defined with `glhd/gretel` (via route `->breadcrumb()` definitions).
   - If the config file isn't published, the default is `DiglacticBreadcrumbsCollector`.
2. **Which package is installed** — check `composer.json` (or use Boost's *Application Info* tool) to confirm the matching package is present. A mismatch (collector configured but package missing) throws `PackageNotInstalledException` on every web request.
3. **The frontend framework** — Vue, React, or Svelte — from the Inertia setup, so rendering examples match the project.

If the project has no breadcrumb setup yet and you're starting fresh, the built-in `ClosureBreadcrumbsCollector` needs no extra dependency; otherwise match the collector to the package already in use.

## Step 2 — Do the task, then load the matching reference

Read only the reference you need:

- **Defining breadcrumbs** (adding/editing breadcrumbs for a page or route) → `references/defining-breadcrumbs.md`. This differs per collector — the closure collector uses this package's `for()` / `Breadcrumb::make()` API; the others are defined in their own package's syntax and merely collected here.
- **Rendering breadcrumbs** (building the trail/nav in a page or layout component) → `references/rendering-breadcrumbs.md`. Covers the prop shape and Vue/React/Svelte examples.
- **Configuring / extending** (share strategy, classifier, custom serialization, middleware key/group, query handling, the gretel caveat) → `references/configuration.md`.

## The breadcrumb prop (quick reference)

Whatever the collector, the shared prop is an array of objects in this shape — enough for most rendering without opening a reference:

```json
[
  { "title": "Dashboard", "url": "https://app.test/dashboard" },
  { "title": "Profile", "url": "https://app.test/dashboard/profile", "current": true },
  { "title": "Breadcrumb without URL" }
]
```

- `title` is always present. `url`, `current`, and `data` are omitted when not set (`null`/`false`/empty).
- The whole prop is `null` when the current route has no breadcrumbs — guard on the value, not the key: `v-if="$page.props.breadcrumbs"`.
- A custom serializer can change these keys (see `references/configuration.md`), so check for `serializeUsing` before assuming the default shape.

## When unsure, read — don't invent

This skill covers the common cases. If a detail isn't here, consult the source instead of guessing at method or config names:

- Package README: <https://github.com/RobertBoes/inertia-breadcrumbs>
- Config: `config/inertia-breadcrumbs.php`
- Key classes in the installed package: `Breadcrumb` (the `make()` factory and accessors), the `Collectors\*` classes, `InertiaBreadcrumbs` (the `for()` / `serializeUsing()` API), and `Classifier\ClassifierContract`.

Do not invent methods, config keys, or prop fields that you cannot find in those places.

## Common mistakes to avoid

- **Assuming the collector or frontend.** Always run Step 1 first; the project may use any of the four collectors and any Inertia frontend.
- **Using `glhd/gretel` without disabling its own Inertia sharing.** Gretel auto-shares a `breadcrumbs` prop too; you must turn that off or you get double/competing data. See `references/configuration.md`.
- **Defining closure-collector breadcrumbs when a third-party collector is configured** (or vice versa). The `for()` / `Breadcrumb::make()` API only applies to `ClosureBreadcrumbsCollector`.
- **Assuming the default prop shape when a custom serializer is registered.**
