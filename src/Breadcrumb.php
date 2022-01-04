<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Illuminate\Contracts\Support\Arrayable;

class Breadcrumb implements Arrayable
{
    private string $title;

    private bool $current;

    private ?string $url;

    private ?array $data;

    public function __construct(string $title, ?bool $current, ?string $url = null, ?array $data = null)
    {
        $this->title = $title;
        $this->current = $current ?? false;
        $this->url = $url;
        $this->data = $data;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function data(): ?array
    {
        return $this->data;
    }

    public function toArray()
    {
        return array_filter([
            'title' => $this->title,
            'url' => $this->url,
            'current' => $this->current,
            'data' => $this->data,
        ]);
    }
}
