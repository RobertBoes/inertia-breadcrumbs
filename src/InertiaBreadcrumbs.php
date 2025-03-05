<?php

namespace RobertBoes\InertiaBreadcrumbs;

class InertiaBreadcrumbs
{
    /**
     * The callback that is responsible serializing breadcrumbs to the frontend
     *
     * @var callable|null
     */
    public static $serializeUsingCallback;

    public static function serializeUsing(callable $callback): void
    {
        static::$serializeUsingCallback = $callback;
    }
}
