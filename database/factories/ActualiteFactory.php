<?php

namespace Database\Factories;

use App\Models\Actualite;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActualiteFactory extends Factory
{
    protected $model = Actualite::class;

    public function definition(): array
    {
        return [
            'title'         => $this->faker->sentence(6),
            'description'   => $this->faker->paragraph(2),
            'content_text'  => $this->faker->paragraphs(5, true),
            'category'      => $this->faker->randomElement([
                'Economie',
                'Finances',
                'Administration',
                'Social',
                'Communiqué',
            ]),
            'posted_in'     => $this->faker->randomElement([
                'Site web',
                'Facebook',
                'Journal officiel',
            ]),
            'published_at'  => $this->faker->optional(0.8)->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * State: published actualité
     */
    public function published(): static
    {
        return $this->state(fn () => [
            'published_at' => now(),
        ]);
    }

    /**
     * State: draft actualité
     */
    public function draft(): static
    {
        return $this->state(fn () => [
            'published_at' => null,
        ]);
    }
}
