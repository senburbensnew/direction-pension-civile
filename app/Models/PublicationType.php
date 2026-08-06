<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PublicationType extends Model
{
    protected $fillable = ['code', 'label', 'icon', 'badge_class', 'order_column'];

    public const CACHE_KEY = 'publication_types_map';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_column')->orderBy('label');
    }

    /** @return array<string, string> code => label */
    public static function options(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return static::ordered()->pluck('label', 'code')->all();
        });
    }

    /** @return array<string, array{0: string, 1: string}> code => [icon, badge_class] */
    public static function visuals(): array
    {
        return static::ordered()
            ->get(['code', 'icon', 'badge_class'])
            ->mapWithKeys(fn ($t) => [$t->code => [$t->icon, $t->badge_class]])
            ->all();
    }

    public static function makeCode(string $label): string
    {
        $base = Str::slug($label, '_');
        $base = $base !== '' ? $base : 'type';
        $code = $base;
        $i = 1;
        while (static::where('code', $code)->exists()) {
            $code = $base . '_' . $i++;
        }
        return $code;
    }

    public function publicationsCount(): int
    {
        return Publication::where('type', $this->code)->count();
    }
}
