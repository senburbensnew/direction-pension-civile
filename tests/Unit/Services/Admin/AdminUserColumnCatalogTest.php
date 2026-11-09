<?php

namespace Tests\Unit\Services\Admin;

use App\Models\User;
use App\Services\Admin\AdminUserColumnCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserColumnCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_apply_when_no_columns_requested(): void
    {
        $catalog = new AdminUserColumnCatalog;

        $this->assertSame(AdminUserColumnCatalog::DEFAULT_COLUMNS, $catalog->resolveSelected(null));
    }

    public function test_selected_columns_keep_request_order(): void
    {
        $catalog = new AdminUserColumnCatalog;

        $selected = $catalog->resolveSelected(['phone', 'user_summary', 'nif', 'roles']);

        $this->assertSame(['phone', 'user_summary', 'nif', 'roles'], $selected);
    }

    public function test_default_sort_uses_user_summary_asc_when_visible(): void
    {
        $catalog = new AdminUserColumnCatalog;

        $active = $catalog->resolveActiveSort(
            AdminUserColumnCatalog::DEFAULT_COLUMNS,
            null,
            'asc',
        );

        $this->assertSame('user_summary', $active['sort_column']);
        $this->assertSame('asc', $active['sort_direction']);
        $this->assertFalse($active['sort_explicit']);
    }

    public function test_date_columns_default_to_descending_sort(): void
    {
        $catalog = new AdminUserColumnCatalog;

        $this->assertSame('desc', $catalog->defaultDirectionFor('created_at'));
        $this->assertSame('asc', $catalog->defaultDirectionFor('email'));
    }

    public function test_service_column_is_sortable(): void
    {
        $catalog = new AdminUserColumnCatalog;

        $this->assertTrue($catalog->isSortable('service'));
        $this->assertTrue($catalog->isSortable('roles'));
        $this->assertTrue($catalog->isSortable('primary_category'));
    }

    public function test_resolve_active_sort_falls_back_to_first_sortable_selected_column(): void
    {
        $catalog = new AdminUserColumnCatalog;

        $active = $catalog->resolveActiveSort(
            ['phone', 'nif', 'roles'],
            null,
            'asc',
        );

        $this->assertSame('phone', $active['sort_column']);
        $this->assertSame('asc', $active['sort_direction']);
        $this->assertFalse($active['sort_explicit']);
    }

    public function test_export_value_for_user_summary_includes_email(): void
    {
        $catalog = new AdminUserColumnCatalog;
        $user = User::factory()->make([
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
        ]);

        $this->assertSame('Jean Dupont (jean@example.com)', $catalog->exportValue($user, 'user_summary'));
    }
}
