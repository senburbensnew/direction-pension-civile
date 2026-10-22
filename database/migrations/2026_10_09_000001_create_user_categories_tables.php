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
};
