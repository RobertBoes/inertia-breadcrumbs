<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class BreadcrumbCollection implements Arrayable
{
    /** @var Collection<int, Breadcrumb> */
    public Collection $items;

    public function __construct(array|Collection $items)
    {
        $this->items = Collection::wrap($items)
            ->map(function (mixed $breadcrumb): Breadcrumb {
                if ($breadcrumb instanceof Breadcrumb) {
                    return $breadcrumb;
                }

                return Breadcrumb::make($breadcrumb);
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
