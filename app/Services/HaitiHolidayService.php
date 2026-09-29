<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HaitiHolidayService
{
    public function calculate(int $year): Collection
    {
        $holidays = collect();

        /*
         * Jours fixes
         */
        $fixed = [
            '01-01' => 'Jour de l’An / Indépendance',
            '01-02' => 'Jour des Aïeux',
            '05-01' => 'Fête du Travail et de l’Agriculture',
            '05-18' => 'Fête du Drapeau et de l’Université',
            '08-14' => 'Fête de Bois-Caïman',
            '08-15' => 'Assomption',
            '09-20' => 'Fête de Dessalines',
            '10-17' => 'Commémoration de la mort de Dessalines',
            '11-01' => 'La Toussaint',
            '11-02' => 'Jour des Morts',
            '11-18' => 'Bataille de Vertières',
            '12-25' => 'Noël',
        ];

        foreach ($fixed as $monthDay => $name) {
            $date = Carbon::createFromFormat(
                'Y-m-d',
                "{$year}-{$monthDay}"
            );

            $holidays->push([
                'date' => $date,
                'name' => $name,
                'country' => 'HT',
                'is_recurring' => true,
                'is_official' => true,
            ]);
        }

        /*
         * Pâques
         */
        $easter = Carbon::createFromTimestamp(
            easter_date($year)
        )->startOfDay();

        /*
         * Carnaval
         *
         * Mardi gras = 47 jours avant Pâques.
         * Lundi gras = 48 jours avant Pâques.
         */
        $holidays->push([
            'date' => $easter->copy()->subDays(48),
            'name' => 'Lundi gras',
            'country' => 'HT',
            'is_recurring' => false,
            'is_official' => true,
        ]);

        $holidays->push([
            'date' => $easter->copy()->subDays(47),
            'name' => 'Mardi gras',
            'country' => 'HT',
            'is_recurring' => false,
            'is_official' => true,
        ]);

        /*
         * Vendredi saint
         */
        $holidays->push([
            'date' => $easter->copy()->subDays(2),
            'name' => 'Vendredi saint',
            'country' => 'HT',
            'is_recurring' => false,
            'is_official' => true,
        ]);

        /*
         * Fête-Dieu / Corpus Christi
         *
         * 60 jours après Pâques.
         */
        $holidays->push([
            'date' => $easter->copy()->addDays(60),
            'name' => 'Fête-Dieu',
            'country' => 'HT',
            'is_recurring' => false,
            'is_official' => true,
        ]);

        return $holidays->sortBy('date')->values();
    }

    public function generate(int $year): void
    {
        foreach ($this->calculate($year) as $holiday) {
            Holiday::updateOrCreate(
                [
                    'date' => $holiday['date']->format('Y-m-d'),
                    'country' => 'HT',
                ],
                [
                    'name' => $holiday['name'],
                    'is_recurring' => $holiday['is_recurring'],
                    'is_official' => $holiday['is_official'],
                ]
            );
        }
    }

    public function generateCurrentAndNextYear(): void
    {
        $currentYear = now()->year;

        $this->generate($currentYear);
        $this->generate($currentYear + 1);
    }

    public function isHoliday(Carbon|string $date): bool
    {
        $date = $date instanceof Carbon
            ? $date
            : Carbon::parse($date);

        return Holiday::haiti()
            ->whereDate('date', $date->toDateString())
            ->exists();
    }

    public function getHoliday(Carbon|string $date): ?Holiday
    {
        $date = $date instanceof Carbon
            ? $date
            : Carbon::parse($date);

        return Holiday::haiti()
            ->whereDate('date', $date->toDateString())
            ->first();
    }
}