<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Illuminate\Contracts\Support\Arrayable;

readonly class Breadcrumb implements Arrayable
{
    public function __construct(
        private string $title,
        private bool $current = false,
        private ?string $url = null,
        private ?array $data = null
    ) {}

    public function title(): string
    {
        return $this->title;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function current(): bool
    {
        return $this->current;
    }

    public function data(): ?array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        if (InertiaBreadcrumbs::$serializeUsingCallback) {
            return call_user_func(InertiaBreadcrumbs::$serializeUsingCallback, $this);
        }

        return array_filter([
            'title' => $this->title(),
            'url' => $this->url(),
            'current' => $this->current(),
            'data' => $this->data(),
        ]);
    }
}
