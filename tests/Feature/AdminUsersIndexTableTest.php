<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Account\UserCategoryRoleSyncService;
use App\Services\Admin\AdminUserColumnCatalog;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class AdminUsersIndexTableTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflowSteps();
        $this->seedServices();
        $this->seedRoles();
        $this->seed(UserCategoriesSeeder::class);
    }

    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    /**
     * @return list<string>
     */
    private function columnKeysFromIndexHtml(string $html): array
    {
        preg_match_all('/data-column-key="([^"]+)"/', $html, $matches);

        return $matches[1] ?? [];
    }

    public function test_index_renders_table_shell_and_server_side_headers(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('data-admin-users-index', false);
        $response->assertSee('adminUsersIndex(', false);
        $response->assertSee('data-column-drag-handle', false);
        $response->assertSee('Utilisateur', false);
        $response->assertSee('Actions', false);
        $response->assertDontSee('x-for="(columnKey', false);

        $this->assertSame(
            AdminUserColumnCatalog::DEFAULT_COLUMNS,
            $this->columnKeysFromIndexHtml($response->getContent()),
        );
    }

    public function test_index_preserves_custom_column_order_in_headers(): void
    {
        $admin = $this->makeAdmin();
        $order = ['phone', 'user_summary', 'email', 'roles'];

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'columns' => $order,
        ]));

        $response->assertOk();
        $this->assertSame($order, $this->columnKeysFromIndexHtml($response->getContent()));
    }

    public function test_index_filters_users_by_search_query(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->create(['email' => 'visible-filter@example.com', 'name' => 'Visible User']);
        User::factory()->create(['email' => 'hidden-filter@example.com', 'name' => 'Hidden User']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'q' => 'visible-filter@',
        ]));

        $response->assertOk();
        $response->assertSee('visible-filter@example.com', false);
        $response->assertDontSee('hidden-filter@example.com', false);
    }

    public function test_index_filters_users_by_role(): void
    {
        $admin = $this->makeAdmin();
        $pensionne = User::factory()->create(['email' => 'pensionne-list@example.com']);
        $pensionne->assignRole('pensionne');
        $other = User::factory()->create(['email' => 'admin-list@example.com']);
        $other->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'role' => 'pensionne',
        ]));

        $response->assertOk();
        $response->assertSee('pensionne-list@example.com', false);
        $response->assertDontSee('admin-list@example.com', false);
    }

    public function test_index_sorts_users_by_name_when_requested(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->create(['name' => 'Zulu Name', 'email' => 'z-sort@example.com']);
        User::factory()->create(['name' => 'Alpha Name', 'email' => 'a-sort@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'sort' => 'user_summary',
            'dir' => 'asc',
        ]));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'Zulu Name'),
            strpos($html, 'Alpha Name'),
        );
        $this->assertStringContainsString('text-indigo-600', $html);
    }

    public function test_index_sorts_by_email_descending(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->create(['email' => 'aaa-sort@example.com', 'name' => 'AAA']);
        User::factory()->create(['email' => 'zzz-sort@example.com', 'name' => 'ZZZ']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'columns' => ['user_summary', 'email'],
            'sort' => 'email',
            'dir' => 'desc',
        ]));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'aaa-sort@example.com'),
            strpos($html, 'zzz-sort@example.com'),
        );
    }

    public function test_index_sorts_by_roles_without_error(): void
    {
        $admin = $this->makeAdmin();
        $userA = User::factory()->create(['email' => 'roles-a@example.com']);
        $userA->syncRoles(['agent_formalites']);
        $userB = User::factory()->create(['email' => 'roles-b@example.com']);
        $userB->syncRoles(['admin']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'sort' => 'roles',
            'dir' => 'asc',
        ]));

        $response->assertOk();
        $response->assertSee('roles-a@example.com', false);
        $response->assertSee('roles-b@example.com', false);
    }

    public function test_index_sorts_by_primary_category_without_error(): void
    {
        $admin = $this->makeAdmin();
        $sync = app(UserCategoryRoleSyncService::class);

        $externe = User::factory()->create(['email' => 'cat-externe@example.com']);
        $sync->syncSingleCategory($externe, 'externe', syncRoles: false);

        $pensionne = User::factory()->create(['email' => 'cat-pensionne@example.com']);
        $sync->syncSingleCategory($pensionne, 'pensionne', syncRoles: false);

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'sort' => 'primary_category',
            'dir' => 'asc',
        ]));

        $response->assertOk();
        $response->assertSee('cat-externe@example.com', false);
        $response->assertSee('cat-pensionne@example.com', false);
    }

    public function test_index_ignores_invalid_sort_column(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->create(['name' => 'Zulu Name', 'email' => 'z-invalid@example.com']);
        User::factory()->create(['name' => 'Alpha Name', 'email' => 'a-invalid@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'sort' => 'not_a_real_column',
            'dir' => 'desc',
        ]));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'Zulu Name'),
            strpos($html, 'Alpha Name'),
        );
    }

    public function test_export_csv_headers_follow_column_order(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->create(['email' => 'order-export@example.com', 'phone' => '+50937000001']);

        $response = $this->actingAs($admin)->get(route('admin.users.export', [
            'columns' => ['phone', 'user_summary', 'email'],
        ]));

        $response->assertOk();
        $lines = preg_split('/\r\n|\n|\r/', trim($response->streamedContent()));
        $header = str_getcsv($lines[0] ?? '', ',', '"', '\\');
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]) ?? $header[0];
        }
        $this->assertSame(['Téléphone', 'Utilisateur', 'E-mail'], $header);
    }

    public function test_export_csv_rows_follow_sort_parameters(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->create(['email' => 'aaa-export@example.com', 'name' => 'AAA Export']);
        User::factory()->create(['email' => 'zzz-export@example.com', 'name' => 'ZZZ Export']);

        $response = $this->actingAs($admin)->get(route('admin.users.export', [
            'columns' => ['user_summary', 'email'],
            'sort' => 'email',
            'dir' => 'desc',
        ]));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertLessThan(
            strpos($content, 'aaa-export@example.com'),
            strpos($content, 'zzz-export@example.com'),
        );
    }

    public function test_export_link_on_index_includes_current_filters_and_columns(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'q' => 'test-export-link',
            'role' => 'admin',
            'columns' => ['user_summary', 'phone'],
            'sort' => 'email',
            'dir' => 'desc',
        ]));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('test-export-link', $html);
        $this->assertStringContainsString('user_summary', $html);
        $this->assertStringContainsString('phone', $html);
        $this->assertStringContainsString('sort=email', $html);
        $this->assertStringContainsString('dir=desc', $html);
        $this->assertStringContainsString(route('admin.users.export'), $html);
    }
}
