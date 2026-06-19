## Inertia Breadcrumbs

This application uses `robertboes/inertia-breadcrumbs` to share breadcrumbs to the frontend as an Inertia shared prop (`breadcrumbs` by default).

When defining breadcrumbs for a page, rendering a breadcrumb trail in an Inertia page component, or choosing/configuring the breadcrumb collector, use the `inertia-breadcrumbs` skill — it checks the project's actual setup before suggesting code, so you don't assume the wrong collector or frontend. If that skill isn't available, read the package's README or source under `vendor/robertboes/inertia-breadcrumbs` before writing code: the API is small but specific, so verify method and config names rather than guessing.
