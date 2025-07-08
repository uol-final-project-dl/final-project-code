<?php

namespace App\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

trait HasSelfCasting
{
    /** @noinspection PhpIncompatibleReturnTypeInspection, PhpUnused */
    public static function instance(Model|Authenticatable|null $model): ?static
    {
        return $model;
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection, PhpUnused */
    public static function safeInstance(Model|Authenticatable $model): static
    {
        return $model;
    }
}
