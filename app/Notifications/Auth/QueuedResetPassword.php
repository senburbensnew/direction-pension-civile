<?php

namespace App\Notifications\Auth;

use App\Queue\ConfiguresQueueRetries;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuedResetPassword extends ResetPassword implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable;

    public function __construct(#[\SensitiveParameter] string $token)
    {
        parent::__construct($token);
        $this->configureQueueRetries();
        $this->afterCommit();
    }
}
