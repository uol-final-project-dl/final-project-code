<?php

namespace App\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;

trait HasMakeAble
{
    /**
     * @throws BindingResolutionException
     */
    public static function make(): static
    {
        return app()->make(__CLASS__);
    }
}
