<?php

namespace Tests\Feature\Admin;

use App\Enums\UserSessionEndReasonEnum;
use App\Models\User;
use App\Models\UserLoginEvent;
use App\Models\UserSessionEndEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserSessionTraceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin');
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_logout_is_recorded_in_session_end_journal(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertDatabaseHas('user_session_end_events', [
            'user_id' => $user->id,
            'reason' => UserSessionEndReasonEnum::VoluntaryLogout->value,
            'initiated_by_user_id' => null,
        ]);
    }

    public function test_admin_revoke_all_records_session_end_with_actor(): void
    {
        if (config('session.driver') !== 'database') {
            $this->markTestSkipped('Requires database session driver.');
        }

        $admin = $this->makeAdmin();
        $target = User::factory()->create();

        $this->actingAs($target)->get('/');

        $this->actingAs($admin)
            ->from(route('admin.users.edit', $target))
            ->delete(route('admin.sessions.destroy-user', $target), [
                'redirect_to' => route('admin.users.edit', $target),
            ])
            ->assertRedirect(route('admin.users.edit', $target));

        $event = UserSessionEndEvent::query()->where('user_id', $target->id)->first();

        $this->assertNotNull($event);
        $this->assertSame(UserSessionEndReasonEnum::AdminRevokeAll, $event->reason);
        $this->assertSame($admin->id, $event->initiated_by_user_id);
    }

    public function test_user_edit_shows_connection_timeline_when_events_exist(): void
    {
        $admin = $this->makeAdmin();
        $target = User::factory()->create();

        UserLoginEvent::query()->create([
            'user_id' => $target->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'logged_in_at' => now()->subHour(),
        ]);

        UserSessionEndEvent::query()->create([
            'user_id' => $target->id,
            'initiated_by_user_id' => $admin->id,
            'reason' => UserSessionEndReasonEnum::PasswordReset,
            'ip_address' => '127.0.0.1',
            'ended_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $target))
            ->assertOk()
            ->assertSee(__('messages.admin_sessions.connection_trace_title'), false)
            ->assertSee(__('messages.admin_sessions.trace_login'), false)
            ->assertSee(UserSessionEndReasonEnum::PasswordReset->label(), false);
    }
}
