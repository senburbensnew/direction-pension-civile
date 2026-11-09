<?php

namespace Tests\Unit\Support;

use App\Support\Eloquent\IncludeSoftDeleted;
use Tests\TestCase;

class IncludeSoftDeletedTest extends TestCase
{
    public function test_enable_and_disable_toggle_flag(): void
    {
        $this->assertFalse(IncludeSoftDeleted::enabled());

        IncludeSoftDeleted::enable();
        $this->assertTrue(IncludeSoftDeleted::enabled());

        IncludeSoftDeleted::disable();
        $this->assertFalse(IncludeSoftDeleted::enabled());
    }
}
