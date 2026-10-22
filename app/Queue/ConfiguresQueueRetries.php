<?php

namespace App\Queue;

trait ConfiguresQueueRetries
{
    protected function configureQueueRetries(): void
    {
        $this->tries = (int) config('queue.max_tries', 5);

        $backoff = config('queue.backoff');

        if (is_array($backoff) && $backoff !== []) {
            $this->backoff = $backoff;
        }
    }
}
