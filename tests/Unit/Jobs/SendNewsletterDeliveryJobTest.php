<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendNewsletterDeliveryJob;
use App\Services\Newsletter\NewsletterDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendNewsletterDeliveryJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function handle_no_ops_when_delivery_missing(): void
    {
        $this->mock(NewsletterDispatchService::class, function ($mock): void {
            $mock->shouldNotReceive('refreshCampaignStats');
        });

        (new SendNewsletterDeliveryJob(999999))->handle(app(NewsletterDispatchService::class));
    }
}
