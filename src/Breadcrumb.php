<?php

namespace RobertBoes\InertiaBreadcrumbs;

use Illuminate\Contracts\Support\Arrayable;
use stdClass;

class Breadcrumb implements Arrayable
{
    public readonly string $title;

    public readonly ?string $url;

    public function __construct(string $title, ?string $url)
    {
        $this->title = $title;
        $this->url = $url;
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
