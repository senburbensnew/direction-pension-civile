<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $validCodes = array_keys(config('account_categories.categories', []));

        if ($validCodes === []) {
            return;
        }

        $invalidCodes = DB::table('user_categories')
            ->whereNotIn('code', $validCodes)
            ->pluck('code');

        if ($invalidCodes->isEmpty()) {
            return;
        }

        $now = now();
        $personnelCode = 'personnel_dpc';

        foreach ($invalidCodes as $invalidCode) {
            $assignments = DB::table('user_category_assignments')
                ->where('user_category_code', $invalidCode)
                ->get(['user_id', 'is_primary']);

            foreach ($assignments as $assignment) {
                $userId = (int) $assignment->user_id;

                $hasPersonnel = DB::table('user_category_assignments')
                    ->where('user_id', $userId)
                    ->where('user_category_code', $personnelCode)
                    ->exists();

                $hasService = DB::table('users')
                    ->where('id', $userId)
                    ->whereNotNull('service_id')
                    ->exists();

                if (! $hasPersonnel && ($hasService || $invalidCode === 'agent_rdv')) {
                    DB::table('user_category_assignments')->insertOrIgnore([
                        'user_id' => $userId,
                        'user_category_code' => $personnelCode,
                        'is_primary' => (bool) $assignment->is_primary,
                        'valid_from' => null,
                        'valid_until' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            DB::table('user_category_assignments')
                ->where('user_category_code', $invalidCode)
                ->delete();
        }

        DB::table('user_categories')
            ->whereNotIn('code', $validCodes)
            ->delete();
    }

    public function down(): void
    {
        // Données parasites non restaurées.
    }
};
