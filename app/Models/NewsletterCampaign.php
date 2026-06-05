<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewsletterCampaign extends Model
{
    use HasFactory;

    protected $fillable = ['subject', 'body', 'recipients_count', 'sent_by', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
