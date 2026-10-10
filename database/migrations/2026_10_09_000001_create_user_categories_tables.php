<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_categories', function (Blueprint $table) {
            $table->string('code', 64)->primary();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(true);
            $table->json('capabilities')->nullable();
            $table->timestamps();
        });

        Schema::create('user_category_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('user_category_code', 64);
            $table->boolean('is_primary')->default(false);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->foreign('user_category_code')
                ->references('code')
                ->on('user_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unique(['user_id', 'user_category_code']);
            $table->index(['user_id', 'is_primary']);
        });

        $this->seedCategoriesFromConfig();
        $this->backfillAssignments();
    }

    public function down(): void
    {
        Schema::dropIfExists('user_category_assignments');
        Schema::dropIfExists('user_categories');
    }

    private function seedCategoriesFromConfig(): void
    {
        $now = now();

        foreach (config('account_categories.categories', []) as $code => $definition) {
            DB::table('user_categories')->insertOrIgnore([
                'code' => $code,
                'label' => $definition['label'] ?? $code,
                'description' => $definition['description'] ?? null,
                'is_system' => $definition['is_system'] ?? true,
                'capabilities' => isset($definition['capabilities'])
                    ? json_encode($definition['capabilities'])
                    : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function backfillAssignments(): void
    {
        if (! Schema::hasColumn('users', 'user_type_id')) {
            return;
        }

        $now = now();

        $rows = DB::table('users')
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->whereNotNull('users.user_type_id')
            ->select('users.id as user_id', 'user_types.name as category_code', 'users.service_id')
            ->get();

        foreach ($rows as $row) {
            $code = $this->normalizeCategoryCode((string) $row->category_code);

            if (! DB::table('user_categories')->where('code', $code)->exists()) {
                continue;
            }

            DB::table('user_category_assignments')->insertOrIgnore([
                'user_id' => $row->user_id,
                'user_category_code' => $code,
                'is_primary' => true,
                'valid_from' => null,
                'valid_until' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $personnelCode = 'personnel_dpc';

        if (! DB::table('user_categories')->where('code', $personnelCode)->exists()) {
            return;
        }

        $staffUserIds = DB::table('users')
            ->whereNotNull('service_id')
            ->pluck('id');

        foreach ($staffUserIds as $userId) {
            DB::table('user_category_assignments')->insertOrIgnore([
                'user_id' => $userId,
                'user_category_code' => $personnelCode,
                'is_primary' => false,
                'valid_from' => null,
                'valid_until' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $hasPersonnel = DB::table('user_category_assignments')
                ->where('user_id', $userId)
                ->where('user_category_code', $personnelCode)
                ->exists();

            if ($hasPersonnel) {
                DB::table('user_category_assignments')
                    ->where('user_id', $userId)
                    ->where('user_category_code', 'fonctionnaire')
                    ->update(['is_primary' => false]);

                DB::table('user_category_assignments')
                    ->where('user_id', $userId)
                    ->where('user_category_code', $personnelCode)
                    ->update(['is_primary' => true]);
            }
        }
    }

    private function normalizeCategoryCode(string $name): string
    {
        return match ($name) {
            'pensionne', 'fonctionnaire', 'institution', 'externe', 'personnel_dpc' => $name,
            default => $name,
        };
    }
};
