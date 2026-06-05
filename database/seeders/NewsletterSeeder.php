<?php

namespace Database\Seeders;

use App\Models\Newsletter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsletterSeeder extends Seeder
{
    public function run(): void
    {
        $emails = [
            'jean.pierre@example.ht',
            'marie.louise@example.ht',
            'pierre.dupont@example.ht',
            'claudette.joseph@example.ht',
            'rodrigue.estimable@example.ht',
            'nadege.celestin@example.ht',
            'frantz.augustin@example.ht',
            'roseline.desir@example.ht',
            'wilfrid.metellus@example.ht',
            'guerline.toussaint@example.ht',
        ];

        foreach ($emails as $email) {
            Newsletter::updateOrCreate(
                ['email' => $email],
                ['unsubscribe_token' => Str::random(64)]
            );
        }
    }
}
