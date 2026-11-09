<?php

namespace Tests\Unit\Support;

use App\Models\FaqItem;
use App\Support\Admin\AdminModelDeletion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminModelDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_delete_archives_record(): void
    {
        $item = FaqItem::query()->create([
            'question' => 'Q?',
            'answer' => 'A.',
            'published' => true,
        ]);

        $message = AdminModelDeletion::apply($item, Request::create('/', 'DELETE'));

        $this->assertStringContainsString('archivé', $message);
        $this->assertSoftDeleted($item);
        $this->assertNull(FaqItem::query()->find($item->id));
    }

    public function test_restore_brings_back_soft_deleted_record(): void
    {
        $item = FaqItem::query()->create([
            'question' => 'Q?',
            'answer' => 'A.',
            'published' => true,
        ]);
        $item->delete();

        AdminModelDeletion::apply($item, Request::create('/', 'DELETE', ['restore' => '1']));

        $this->assertNotNull(FaqItem::query()->find($item->id));
        $this->assertNull($item->fresh()->deleted_at);
    }

    public function test_permanent_deletes_soft_deleted_record(): void
    {
        $item = FaqItem::query()->create([
            'question' => 'Q?',
            'answer' => 'A.',
            'published' => true,
        ]);
        $id = $item->id;
        $item->delete();

        AdminModelDeletion::apply($item, Request::create('/', 'DELETE', ['permanent' => '1']));

        $this->assertDatabaseMissing('faq_items', ['id' => $id]);
    }
}
