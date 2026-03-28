<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeDocument extends Model
{
    protected $table = 'demande_documents';

    // public const CAREER_CERTIFICATE = 'career_certificates';
    // public const MEDICAL_CERTIFICATE = 'medical_certificate';

    protected $fillable = [
        'demande_id',
        'type',
        'label',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    /* ================= Relations ================= */

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    /* ================= Helpers ================= */

    public function url(): string
    {
        return \Storage::disk($this->disk)->url($this->path);
    }

    public function sizeInKo(): float
    {
        return round($this->size / 1024, 2);
    }

    public function sizeInMo(): float
    {
        return round($this->size / 1024 / 1024, 2);
    }
}
