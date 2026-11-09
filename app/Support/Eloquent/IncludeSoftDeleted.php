<?php

namespace App\Support\Eloquent;

final class IncludeSoftDeleted
{
    private const CONTAINER_KEY = 'eloquent.include_soft_deleted';

    public static function enable(): void
    {
        app()->instance(self::CONTAINER_KEY, true);
    }

    public static function enabled(): bool
    {
        return app()->bound(self::CONTAINER_KEY)
            && app(self::CONTAINER_KEY) === true;
    }

    public static function disable(): void
    {
        if (app()->bound(self::CONTAINER_KEY)) {
            app()->forgetInstance(self::CONTAINER_KEY);
        }
    }
}
