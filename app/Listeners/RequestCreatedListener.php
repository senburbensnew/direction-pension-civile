<?php

namespace App\Listeners;

use App\Events\RequestCreated;
use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RequestCreatedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RequestCreated $event): void
    {
        $request = $event->request;
        // $type = class_basename($request);
    }
}
