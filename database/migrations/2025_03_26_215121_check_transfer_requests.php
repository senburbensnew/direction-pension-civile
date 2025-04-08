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
            $table->string('fiscal_year')->nullable();
            $table->string('start_month', 7);
            $table->date('request_date');
            $table->string('pensioner_signature')->nullable();
            $table->foreignId('pension_category_id')->constrained('pension_categories')->onDelete('restrict');
            $table->string('pensioner_code');
            $table->decimal('amount', 10, 2);
            $table->string('lastname');
            $table->string('firstname');
            $table->string('maiden_name');
            $table->string('nif');            
            $table->string('ninu');
            $table->string('address');
            $table->string('email');
            $table->string('phone');
            $table->date('from');
            $table->date('to');
            $table->text('transfer_reason');
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->nullable(false);
            $table->foreignId('status_id')->constrained('statuses')->onDelete('restrict');
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
