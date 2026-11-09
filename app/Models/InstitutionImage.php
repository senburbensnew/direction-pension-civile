<?php

namespace App\Models;

use App\Models\Concerns\IncludesTrashedInAdmin;

use Illuminate\Database\Eloquent\SoftDeletes;

use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Model;

class InstitutionImage extends Model
{
    use SoftDeletes;
    use IncludesTrashedInAdmin;

    protected $fillable = ['title', 'image', 'order', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function imageUrl(): string
    {
        return PublicMedia::preferredUrl($this->mediaSources());
    }

    /**
     * @return array{url: string, webp: ?string, width: ?int, height: ?int}
     */
    public function mediaSources(): array
    {
        return PublicMedia::forUploadedOrStatic($this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function activeOrdered()
    {
        return static::query()->active()->ordered()->get();
    }
}
