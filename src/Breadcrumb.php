<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
readonly class Breadcrumb implements Arrayable
{
    /**
     * @param  ?array<string, mixed>  $data
     */
    public function __construct(
        private string $title,
        private bool $current = false,
        private ?string $url = null,
        private ?array $data = null
    ) {}

    /**
     * @param  ?array<string, mixed>  $data
     */
    public static function make(string $title, ?string $url = null, ?array $data = null): self
    {
        return new self(title: $title, url: $url, data: $data);
    }

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

    /** @return ?array<string, mixed> */
    public function data(): ?array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        $serializer = app(InertiaBreadcrumbs::class);

        if ($serializer->hasCustomSerializer()) {
            return $serializer->serialize($this);
        }

        return array_filter([
            'title' => $this->title(),
            'url' => $this->url(),
            'current' => $this->current(),
            'data' => $this->data(),
        ]);
    }
}
