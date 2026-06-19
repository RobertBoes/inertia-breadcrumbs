# Defining breadcrumbs

How you define breadcrumbs depends on the configured collector (see Step 1 of the skill). This package only *collects and shares* — for the three third-party collectors, breadcrumbs are defined in that package's own syntax.

## Closure collector (built-in, no extra dependency)

When `config('inertia-breadcrumbs.collector')` is `ClosureBreadcrumbsCollector`, define breadcrumbs with this package's own API. Breadcrumbs are keyed by route name.

`Breadcrumb::make()` signature:

```php
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;

Breadcrumb::make(
    string $title,
    ?string $url = null,   // optional; omit for a label-only crumb
    ?array $data = null,    // optional extra payload (e.g. an icon)
    bool $current = false,  // usually leave false — it's auto-detected by URL match
): self;
```

### In a service provider (define once, by route name)

```php
use App\Models\User;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;

public function boot(): void
{
    $breadcrumbs = app(InertiaBreadcrumbs::class);

    $breadcrumbs->for('users.index', fn () => [
        Breadcrumb::make('Users', route('users.index')),
    ]);

    // Route parameters of the current route are passed to the closure, in order.
    $breadcrumbs->for('users.show', fn (User $user) => [
        Breadcrumb::make('Users', route('users.index')),
        Breadcrumb::make($user->name, route('users.show', $user)),
    ]);
}
```

### In a controller (shorthand — route name inferred)

Pass the closure as the first argument and the current route's name is inferred from the request:

```php
use App\Models\User;
use RobertBoes\InertiaBreadcrumbs\Breadcrumb;
use RobertBoes\InertiaBreadcrumbs\InertiaBreadcrumbs;

public function show(User $user, InertiaBreadcrumbs $breadcrumbs)
{
    $breadcrumbs->for(fn (User $user) => [
        Breadcrumb::make('Users', route('users.index')),
        Breadcrumb::make($user->name, route('users.show', $user)),
    ]);

    return inertia('Users/Show', ['user' => $user]);
}
```

Notes:
- The shorthand only works on **named** routes (the name is read from the request). On unnamed routes the breadcrumbs are stored as pending and resolved for the current request only.
- `current` is determined automatically by comparing each breadcrumb's URL with the current request URL. Set it explicitly only when needed: `Breadcrumb::make('Title', $url, current: true)`.
- A label-only crumb (no link) omits the URL: `Breadcrumb::make('Current page')`.
- Attach extra data for the frontend via the `data` argument: `Breadcrumb::make('Users', route('users.index'), ['icon' => 'users'])`.

## Third-party collectors — define in their own syntax

When one of these is configured, do **not** use `InertiaBreadcrumbs::for()`. Define breadcrumbs the way that package documents; this package collects whatever it produces for the current route.

- **`DiglacticBreadcrumbsCollector`** → define with `diglactic/laravel-breadcrumbs`, typically in `routes/breadcrumbs.php` using `Breadcrumbs::for('name', fn (Trail $trail) => ...)`.
- **`TabunaBreadcrumbsCollector`** → define with `tabuna/breadcrumbs` (e.g. `Breadcrumbs::for(...)` / route-attached definitions).
- **`GretelBreadcrumbsCollector`** → define with `glhd/gretel` via route definitions, e.g. `->breadcrumb('Title')` chained on the route. Also requires disabling gretel's own Inertia sharing (see `configuration.md`).

For the exact API of these third-party packages, consult their own documentation — do not guess. This package's responsibility ends at collecting and sharing what they generate.
