<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use RobertBoes\InertiaBreadcrumbs\Exceptions\CannotCreateBreadcrumbException;

class BreadcrumbCollection implements Arrayable
{
    /** @var Collection<int, Breadcrumb> */
    public Collection $items;

    /**
     *
     * @param array|Collection $items
     * @param null|Closure(mixed): Breadcrumb $initializer
     * @return void
     */
    public function __construct(array|Collection $items, ?Closure $initializer = null)
    {
        $this->items = Collection::wrap($items)
            ->map(function (mixed $breadcrumb) use ($initializer): Breadcrumb {
                if ($breadcrumb instanceof Breadcrumb) {
                    return $breadcrumb;
                }

                if ($initializer && ($result = $initializer($breadcrumb)) instanceof Breadcrumb) {
                    return $result;
                }

                throw new CannotCreateBreadcrumbException();
            });
    }

    /**
     * @return Collection<int, Breadcrumb>
     */
    public function items(): Collection
    {
        return $this->items->values();
    }

    public function toArray()
    {
        return $this->items
            ->toArray();
    }
}
