<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Illuminate\Contracts\Support\Arrayable;
use stdClass;

class Breadcrumb implements Arrayable
{
    private string $title;

    private ?string $url;

    public function __construct(string $title, ?string $url)
    {
        $this->title = $title;
        $this->url = $url;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public static function make(stdClass $data): self
    {
        return new self($data->title, $data->url);
    }

    public function toArray()
    {
        return array_filter([
            'title' => $this->title,
            'url' => $this->url,
        ]);
    }
}
