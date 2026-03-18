<?php

namespace RobertBoes\InertiaBreadcrumbs;

class InertiaBreadcrumbs
{
    private ?\Closure $serializeUsingCallback = null;

    /** @var array<string, \Closure> */
    private array $breadcrumbs = [];

    public function serializeUsing(\Closure $callback): void
    {
        $this->serializeUsingCallback = $callback;
    }

    public function hasCustomSerializer(): bool
    {
        return $this->serializeUsingCallback !== null;
    }

    /** @return array<string, mixed> */
    public function serialize(Breadcrumb $breadcrumb): array
    {
        return ($this->serializeUsingCallback)($breadcrumb);
    }

    public function for(string|\Closure $name, ?\Closure $callback = null): void
    {
        if ($name instanceof \Closure) {
            $callback = $name;
            $name = request()->route()?->getName();
        }

        if ($name === null || $callback === null) {
            return;
        }

        $this->breadcrumbs[$name] = $callback;
    }

    public function has(string $name): bool
    {
        return isset($this->breadcrumbs[$name]);
    }

    public function get(string $name): ?\Closure
    {
        return $this->breadcrumbs[$name] ?? null;
    }
}
