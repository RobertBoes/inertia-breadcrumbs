<?php

namespace RobertBoes\InertiaBreadcrumbs\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RobertBoes\InertiaBreadcrumbs\Tests\Database\Factories\UserFactory;

class User extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
