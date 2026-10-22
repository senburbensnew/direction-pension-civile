<?php

namespace App\Jobs;

use App\Queue\ConfiguresQueueRetries;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $to,
        public string $message,
    ) {
        $this->configureQueueRetries();
        $this->afterCommit();
    }

    public function handle(SmsService $sms): void
    {
        $sms->deliver($this->to, $this->message);
    }
}
