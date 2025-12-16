<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $year = $this->faker->numberBetween(2015, now()->year);

        // Fake file info
        $fileName = Str::slug($this->faker->sentence(3)) . '.pdf';
        $filePath = "reports/{$year}/{$fileName}";

        return [
            'title'         => $this->faker->sentence(5),
            'year'          => $year,
            'description'   => $this->faker->paragraph(3),
            'file_name'     => $fileName,
            'file_path'     => $filePath,
            'mime_type'     => 'application/pdf',
            'file_size'     => $this->faker->numberBetween(100_000, 5_000_000), // bytes
            'status'        => $this->faker->randomElement(['draft', 'published', 'archived']),
            'published_at'  => $this->faker->optional(0.7)->dateTimeBetween('-3 years', 'now'),
            'created_by'    => User::factory(),
        ];
    }

    /**
     * State: published report
     */
    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * State: draft report
     */
    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
