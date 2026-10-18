<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->string('status', 32)->default('subscribed')->after('email');
            $table->string('locale', 5)->nullable()->after('status');
            $table->string('audience_group', 32)->nullable()->after('locale');
            $table->json('preferences')->nullable()->after('audience_group');
            $table->foreignId('user_id')->nullable()->after('preferences')->constrained()->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable()->after('user_id');
            $table->timestamp('unsubscribed_at')->nullable()->after('confirmed_at');
            $table->string('consent_source', 64)->nullable()->after('unsubscribed_at');
            $table->string('consent_ip', 45)->nullable()->after('consent_source');
            $table->timestamp('consented_at')->nullable()->after('consent_ip');
            $table->string('confirmation_token', 64)->nullable()->unique()->after('unsubscribe_token');
            $table->timestamp('confirmation_token_expires_at')->nullable()->after('confirmation_token');
        });

        DB::table('newsletters')->whereNull('confirmed_at')->update([
            'confirmed_at' => DB::raw('created_at'),
            'status' => 'subscribed',
        ]);

        Schema::create('newsletter_suppressions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('reason', 32);
            $table->json('meta')->nullable();
            $table->timestamp('suppressed_at')->useCurrent();
            $table->timestamps();
        });

        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            $table->string('status', 32)->default('sent')->after('id');
            $table->string('preview_text')->nullable()->after('subject');
            $table->string('from_name')->nullable()->after('preview_text');
            $table->string('reply_to')->nullable()->after('from_name');
            $table->longText('html_body')->nullable()->after('body');
            $table->json('audience_filter')->nullable()->after('html_body');
            $table->timestamp('scheduled_at')->nullable()->after('audience_filter');
            $table->timestamp('started_at')->nullable()->after('scheduled_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            $table->foreignId('duplicated_from_id')->nullable()->after('cancelled_at')
                ->constrained('newsletter_campaigns')->nullOnDelete();
            $table->string('dispatch_token', 64)->nullable()->unique()->after('duplicated_from_id');
            $table->boolean('open_tracking_enabled')->default(false)->after('dispatch_token');
            $table->boolean('click_tracking_enabled')->default(false)->after('open_tracking_enabled');
            $table->unsignedInteger('count_queued')->default(0)->after('recipients_count');
            $table->unsignedInteger('count_sent')->default(0)->after('count_queued');
            $table->unsignedInteger('count_delivered')->default(0)->after('count_sent');
            $table->unsignedInteger('count_failed')->default(0)->after('count_delivered');
            $table->unsignedInteger('count_bounced')->default(0)->after('count_failed');
            $table->unsignedInteger('count_opened')->default(0)->after('count_bounced');
            $table->unsignedInteger('count_clicked')->default(0)->after('count_opened');
        });

        DB::table('newsletter_campaigns')->update([
            'status' => 'sent',
            'html_body' => DB::raw('body'),
            'completed_at' => DB::raw('sent_at'),
        ]);

        Schema::create('newsletter_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_campaign_id')->constrained('newsletter_campaigns')->cascadeOnDelete();
            $table->foreignId('newsletter_id')->nullable()->constrained('newsletters')->nullOnDelete();
            $table->string('email');
            $table->string('status', 32)->default('queued');
            $table->string('tracking_token', 64)->unique();
            $table->string('provider_message_id')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('failure_reason')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->unsignedSmallInteger('open_count')->default(0);
            $table->unsignedSmallInteger('click_count')->default(0);
            $table->timestamps();

            $table->unique(['newsletter_campaign_id', 'email']);
            $table->index(['newsletter_campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_deliveries');

        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            $table->dropForeign(['duplicated_from_id']);
            $table->dropColumn([
                'status', 'preview_text', 'from_name', 'reply_to', 'html_body', 'audience_filter',
                'scheduled_at', 'started_at', 'completed_at', 'cancelled_at', 'duplicated_from_id',
                'dispatch_token', 'open_tracking_enabled', 'click_tracking_enabled',
                'count_queued', 'count_sent', 'count_delivered', 'count_failed', 'count_bounced',
                'count_opened', 'count_clicked',
            ]);
        });

        Schema::dropIfExists('newsletter_suppressions');

        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'status', 'locale', 'audience_group', 'preferences', 'user_id',
                'confirmed_at', 'unsubscribed_at', 'consent_source', 'consent_ip', 'consented_at',
                'confirmation_token', 'confirmation_token_expires_at',
            ]);
        });
    }
};
