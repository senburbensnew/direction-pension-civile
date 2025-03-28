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
        Schema::create('check_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Unique request code');
            $table->string('fiscal_year');
            $table->string('start_month', 7);
            $table->date('request_date');
            $table->foreignId('pension_category_id')->constrained('pension_categories')->onDelete('cascade');
            $table->string('pensioner_code');
            $table->decimal('amount', 10, 2);
            $table->string('lastname');
            $table->string('firstname');
            $table->string('maiden_name');
            $table->string('nif');            
            $table->string('ninu');
            $table->string('address');
            $table->string('phone');
            $table->string('from');
            $table->string('to');
            $table->text('transfer_reason');
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->nullable(false);
                $table->foreignId('status_id')->constrained('statuses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
