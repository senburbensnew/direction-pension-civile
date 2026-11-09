<?php

namespace App\Models\Concerns;

use App\Support\Eloquent\IncludeSoftDeleted;
use Illuminate\Database\Eloquent\Builder;
trait IncludesTrashedInAdmin
{
    /**
     * parent::newQuery() enregistre d’abord SoftDeletingScope ; withTrashed() le retire
     * avant applyScopes(), donc les lignes archivées sont visibles en admin.
     */
    public function newQuery(): Builder
    {
        $builder = parent::newQuery();

        if (IncludeSoftDeleted::enabled()) {
            $builder->withTrashed();
        }

        return $builder;
    }

    public function resolveRouteBindingQuery($query, $value, $field = null): Builder
    {
        $query = parent::resolveRouteBindingQuery($query, $value, $field);

        if (IncludeSoftDeleted::enabled()) {
            return $query->withTrashed();
        }

        return $query;
    }

    public function resolveChildRouteBindingQuery($childType, $value, $field): Builder
    {
        $query = parent::resolveChildRouteBindingQuery($childType, $value, $field);

        if (IncludeSoftDeleted::enabled()) {
            return $query->withTrashed();
        }

        return $query;
    }
}
