<?php

namespace RobertBoes\InertiaBreadcrumbs;

enum ShareStrategy: string
{
    case Default = 'default';
    case Always = 'always';
    case Deferred = 'deferred';
}
