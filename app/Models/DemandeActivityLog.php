<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeActivityLog extends Model
{
    protected $fillable = [
        'demande_id',
        'user_id',
        'action',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function actionLabel(string $action): string
    {
        return match ($action) {
            'viewed'      => 'Consulté',
            'transferred' => 'Transféré',
            'printed'     => 'Imprimé',
            'downloaded'  => 'Téléchargé',
            default       => $action,
        };
    }
}
