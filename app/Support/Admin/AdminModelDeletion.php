<?php

namespace App\Support\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

final class AdminModelDeletion
{
    public static function appliesSoftDeletes(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }

    /**
     * Soft-delete, force-delete, or restore (admin) according to the request.
     */
    public static function apply(Model $model, Request $request): string
    {
        if ($request->boolean('restore')) {
            if (self::appliesSoftDeletes($model) && $model->trashed()) {
                $model->restore();

                return 'Élément restauré.';
            }

            return 'Aucune restauration effectuée.';
        }

        if ($request->boolean('permanent')) {
            if (self::appliesSoftDeletes($model)) {
                $model->forceDelete();

                return 'Élément supprimé définitivement.';
            }

            $model->delete();

            return 'Élément supprimé.';
        }

        if (self::appliesSoftDeletes($model) && $model->trashed()) {
            return 'Cet élément est déjà archivé. Restaurez-le ou supprimez-le définitivement.';
        }

        $model->delete();

        return self::appliesSoftDeletes($model)
            ? 'Élément archivé (récupérable en administration).'
            : 'Élément supprimé.';
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<int|string>  $ids
     */
    public static function destroyIds(string $modelClass, array $ids, bool $permanent): void
    {
        if ($ids === []) {
            return;
        }

        /** @var Model $instance */
        $instance = new $modelClass;
        $key = $instance->getKeyName();

        $modelClass::withTrashed()
            ->whereIn($key, $ids)
            ->get()
            ->each(function (Model $model) use ($permanent) {
                if ($permanent && self::appliesSoftDeletes($model)) {
                    $model->forceDelete();
                } else {
                    $model->delete();
                }
            });
    }
}
