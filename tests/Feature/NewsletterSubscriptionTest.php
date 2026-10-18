<?php

namespace Tests\Feature;

use App\Enums\NewsletterSubscriberStatusEnum;
use App\Models\Newsletter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribe_with_double_opt_in_creates_pending_subscriber(): void
    {
        Config::set('newsletter.double_opt_in', true);
        Notification::fake();

        $response = $this->post(route('newsletter.souscription'), [
            'email' => 'Test@Example.COM',
            'locale' => 'fr',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('newsletters', [
            'email' => 'test@example.com',
            'status' => NewsletterSubscriberStatusEnum::Pending->value,
        ]);
    }

    public function test_confirm_activates_subscriber(): void
    {
        Config::set('newsletter.double_opt_in', true);

        $subscriber = Newsletter::create([
            'email' => 'confirm@example.com',
            'status' => NewsletterSubscriberStatusEnum::Pending,
            'unsubscribe_token' => Newsletter::generateToken(),
            'confirmation_token' => 'abc123',
            'confirmation_token_expires_at' => now()->addDay(),
        ]);

        $response = $this->get(route('newsletter.confirm', ['token' => 'abc123']));

        $response->assertOk();
        $subscriber->refresh();
        $this->assertSame(NewsletterSubscriberStatusEnum::Subscribed, $subscriber->status);
        $this->assertNotNull($subscriber->confirmed_at);
    }
}
