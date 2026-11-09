<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\IncludeSoftDeletedInAdmin;
use App\Models\ContactSubject;
use App\Support\Eloquent\IncludeSoftDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class IncludeSoftDeletedInAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_middleware_includes_soft_deleted_models_in_queries(): void
    {
        $subject = ContactSubject::query()->create([
            'label' => 'Test',
            'slug' => 'test-soft-delete',
        ]);
        $subject->delete();

        $this->assertNull(ContactSubject::query()->where('slug', 'test-soft-delete')->first());

        $middleware = new IncludeSoftDeletedInAdmin;
        $middleware->handle(Request::create('/admin/contacts', 'GET'), function () {
            $found = ContactSubject::query()->where('slug', 'test-soft-delete')->first();
            $this->assertNotNull($found);
            $this->assertNotNull($found->deleted_at);

            return response('ok');
        });

        $this->assertFalse(IncludeSoftDeleted::enabled());
    }
}
