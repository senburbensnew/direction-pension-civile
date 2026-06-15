<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('etats', 'service_id')) {
            return;
        }

        // SQLite does not support dropForeign — recreate the table without the column
        if (config('database.default') === 'sqlite') {
            $rows = \Illuminate\Support\Facades\DB::table('etats')->get();
            Schema::drop('etats');
            Schema::create('etats', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name')->nullable();
                $table->string('label')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
            foreach ($rows as $row) {
                \Illuminate\Support\Facades\DB::table('etats')->insert([
                    'id'          => $row->id,
                    'code'        => $row->code,
                    'name'        => $row->name ?? null,
                    'label'       => $row->label ?? null,
                    'description' => $row->description ?? null,
                    'created_at'  => $row->created_at ?? now(),
                    'updated_at'  => $row->updated_at ?? now(),
                ]);
            }
            return;
        }

        Schema::table('etats', function (Blueprint $table) {
            try { $table->dropForeign(['service_id']); } catch (\Throwable) {}
            $table->dropColumn('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('etats', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('code');
        });
    }
};
