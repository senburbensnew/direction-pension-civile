<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('status', 32)->default('sent');
            $table->string('subject');
            $table->string('preview_text')->nullable();
            $table->string('from_name')->nullable();
            $table->string('reply_to')->nullable();
            $table->longText('body');
            $table->longText('html_body')->nullable();
            $table->json('audience_filter')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('duplicated_from_id')->nullable()
                ->constrained('newsletter_campaigns')->nullOnDelete();
            $table->string('dispatch_token', 64)->nullable()->unique();
            $table->boolean('open_tracking_enabled')->default(false);
            $table->boolean('click_tracking_enabled')->default(false);
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('count_queued')->default(0);
            $table->unsignedInteger('count_sent')->default(0);
            $table->unsignedInteger('count_delivered')->default(0);
            $table->unsignedInteger('count_failed')->default(0);
            $table->unsignedInteger('count_bounced')->default(0);
            $table->unsignedInteger('count_opened')->default(0);
            $table->unsignedInteger('count_clicked')->default(0);
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_campaigns');
    }
};
