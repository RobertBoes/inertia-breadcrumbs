# Rendering breadcrumbs

Breadcrumbs are shared as the `breadcrumbs` Inertia prop (the key is configurable — see `configuration.md`). Render them in a page or layout component.

## Prop shape

An array of objects; `title` is always present, `url`/`current`/`data` are optional:

```ts
type Breadcrumb = {
  title: string
  url?: string      // omitted for label-only crumbs
  current?: boolean // omitted when false
  data?: Record<string, unknown> // omitted when none
}
```

The whole prop is `null` when the current route has no breadcrumbs, so guard on the value (not the key's existence). If a custom serializer is registered the keys differ — confirm before relying on these names.

## Vue 3

```vue
<template>
  <nav v-if="$page.props.breadcrumbs" aria-label="Breadcrumb">
    <ol>
      <li v-for="crumb in $page.props.breadcrumbs" :key="crumb.title">
        <a v-if="crumb.url" :href="crumb.url" :aria-current="crumb.current ? 'page' : undefined">
          {{ crumb.title }}
        </a>
        <span v-else>{{ crumb.title }}</span>
      </li>
    </ol>
  </nav>
</template>
```

## React

```tsx
import { usePage } from '@inertiajs/react'

export default function Breadcrumbs() {
  const { breadcrumbs } = usePage().props

  if (!breadcrumbs) return null

  return (
    <nav aria-label="Breadcrumb">
      <ol>
        {breadcrumbs.map((crumb) => (
          <li key={crumb.title}>
            {crumb.url ? (
              <a href={crumb.url} aria-current={crumb.current ? 'page' : undefined}>
                {crumb.title}
              </a>
            ) : (
              <span>{crumb.title}</span>
            )}
          </li>
        ))}
      </ol>
    </nav>
  )
}
```

## Svelte

```svelte
<script>
  import { page } from '@inertiajs/svelte'
  $: breadcrumbs = $page.props.breadcrumbs
</script>

{#if breadcrumbs}
  <nav aria-label="Breadcrumb">
    <ol>
      {#each breadcrumbs as crumb (crumb.title)}
        <li>
          {#if crumb.url}
            <a href={crumb.url} aria-current={crumb.current ? 'page' : undefined}>{crumb.title}</a>
          {:else}
            <span>{crumb.title}</span>
          {/if}
        </li>
      {/each}
    </ol>
  </nav>
{/if}
```

## Notes

- Access any extra payload via `crumb.data` (e.g. `crumb.data?.icon`) when you defined breadcrumbs with a `data` argument.
- Match the project's existing styling/markup conventions rather than imposing a fixed structure — these examples are minimal and unstyled on purpose.
- If breadcrumbs aren't appearing: confirm the middleware is active and the current route actually has breadcrumbs defined for the configured collector; the prop is `null` (not an error) when there are none.
