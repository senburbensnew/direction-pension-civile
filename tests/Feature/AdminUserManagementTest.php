<?php

namespace Tests\Feature;

use App\Enums\AccountStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Mail\AdminUserTemporaryPasswordMail;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\UserCategoriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    private const VALID_PHONE = '+50938123456';

    private const VALID_NIF = '123-456-789-0';

    private const VALID_NINU = '1148519577';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedWorkflowSteps();
        $this->seedServices();
        $this->seedRoles();
        $this->seed(UserCategoriesSeeder::class);
        $this->seedGenders();
    }

    /** @return array<string, mixed> */
    private function identityFields(array $overrides = []): array
    {
        return array_merge([
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'gender_id' => $this->defaultGenderId(),
        ], $overrides);
    }

    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_admin_can_open_create_and_edit_user_forms(): void
    {
        $admin = $this->makeAdmin();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Créer un utilisateur', false);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $target))
            ->assertOk()
            ->assertSee($target->email, false);
    }

    public function test_admin_user_forms_do_not_include_pension_code_field(): void
    {
        $admin = $this->makeAdmin();
        $target = User::factory()->create();

        $create = $this->actingAs($admin)->get(route('admin.users.create'));
        $create->assertOk();
        $this->assertStringNotContainsString('name="pension_code"', $create->getContent());
        $this->assertStringNotContainsString('Code pension', $create->getContent());

        $edit = $this->actingAs($admin)->get(route('admin.users.edit', $target));
        $edit->assertOk();
        $this->assertStringNotContainsString('name="pension_code"', $edit->getContent());
        $this->assertStringNotContainsString('Code pension', $edit->getContent());
    }

    public function test_admin_create_user_defaults_display_name_from_first_and_last_name(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'firstname' => 'Marie',
            'lastname' => 'Curie',
            'email' => 'marie-curie@example.com',
            'phone' => self::VALID_PHONE,
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => ['pensionne'],
            'primary_category' => 'pensionne',
        ]));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasNoErrors();

        $created = User::query()->where('email', 'marie-curie@example.com')->firstOrFail();
        $this->assertSame('Marie Curie', $created->name);
        $this->assertSame('Marie Curie', $created->displayName());
    }

    public function test_admin_cannot_create_user_without_display_name(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'email' => 'sans-nom@example.com',
            'phone' => self::VALID_PHONE,
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => ['pensionne'],
            'primary_category' => 'pensionne',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('users', ['email' => 'sans-nom@example.com']);
    }

    public function test_admin_can_create_user_with_nif_and_ninu(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'name' => 'Pensionné Test',
            'email' => 'pensionne-admin@example.com',
            'phone' => self::VALID_PHONE,
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => ['pensionne'],
            'primary_category' => 'pensionne',
            'nif' => self::VALID_NIF,
            'ninu' => self::VALID_NINU,
        ]));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasNoErrors();

        $created = User::query()->where('email', 'pensionne-admin@example.com')->first();
        $this->assertNotNull($created);
        $this->assertSame(self::VALID_NIF, $created->nif);
        $this->assertSame(self::VALID_NINU, $created->ninu);
        $this->assertTrue($created->must_change_password);
        $this->assertTrue($created->hasRole('pensionne'));
        $this->assertTrue($created->hasCategory('pensionne'));
    }

    public function test_admin_create_pensionne_provisions_beneficiaire_without_affiliation(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'name' => 'Sans Code',
            'email' => 'sans-code@example.com',
            'phone' => self::VALID_PHONE,
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => ['pensionne'],
            'primary_category' => 'pensionne',
        ]))->assertRedirect(route('admin.users.index'));

        $created = User::query()->where('email', 'sans-code@example.com')->firstOrFail();

        $link = $created->beneficiaireLinks()
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first();
        $this->assertNotNull($link);
        $this->assertDatabaseMissing('affiliation_pensions', [
            'beneficiaire_id' => $link->beneficiaire_id,
        ]);
    }

    public function test_admin_cannot_create_user_with_invalid_nif_or_ninu(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'email' => 'bad-nif@example.com',
            'phone' => self::VALID_PHONE,
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => ['pensionne'],
            'primary_category' => 'pensionne',
            'nif' => '1234567890',
        ]))->assertSessionHasErrors('nif');

        $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'email' => 'bad-ninu@example.com',
            'phone' => self::VALID_PHONE,
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => ['pensionne'],
            'primary_category' => 'pensionne',
            'ninu' => 'NINU-001',
        ]))->assertSessionHasErrors('ninu');
    }

    public function test_admin_cannot_create_user_with_duplicate_phone(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->create(['phone' => self::VALID_PHONE]);

        $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'email' => 'duplicate-phone@example.com',
            'phone' => self::VALID_PHONE,
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => ['pensionne'],
            'primary_category' => 'pensionne',
        ]))->assertSessionHasErrors('phone');
    }

    public function test_admin_cannot_create_user_without_phone(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'name' => 'Sans téléphone',
            'email' => 'sans-phone@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => ['pensionne'],
            'primary_category' => 'pensionne',
        ]));

        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseMissing('users', ['email' => 'sans-phone@example.com']);
    }

    public function test_admin_cannot_create_internal_role_without_service(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $this->identityFields([
            'name' => 'Agent sans service',
            'email' => 'agent-no-service@example.com',
            'phone' => self::VALID_PHONE,
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => [User::ROLE_AGENT_RDV],
        ]));

        $response->assertSessionHasErrors('service_id');
        $this->assertDatabaseMissing('users', ['email' => 'agent-no-service@example.com']);
    }

    public function test_admin_can_update_user_profile_and_roles(): void
    {
        $admin = $this->makeAdmin();
        $service = Service::first();

        $target = User::factory()->create([
            'name' => 'Avant',
            'nif' => null,
            'service_id' => $service->id,
        ]);
        $target->syncRoles([User::ROLE_AGENT_RDV]);
        app(\App\Services\Account\UserCategoryRoleSyncService::class)
            ->syncSingleCategory($target, 'personnel_dpc', syncRoles: false);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $target), $this->identityFields([
            'name' => 'Après',
            'email' => $target->email,
            'phone' => self::VALID_PHONE,
            'service_id' => $service->id,
            'primary_category' => 'personnel_dpc',
            'roles' => [User::ROLE_AGENT_FORMALITES],
            'nif' => '998-877-665-5',
            'ninu' => '9988776655',
        ]));

        $response->assertRedirect(route('admin.users.edit', $target));
        $response->assertSessionHasNoErrors();

        $target->refresh();
        $this->assertSame('Après', $target->name);
        $this->assertSame('998-877-665-5', $target->nif);
        $this->assertSame('9988776655', $target->ninu);
        $this->assertTrue($target->hasRole(User::ROLE_AGENT_FORMALITES));
        $this->assertFalse($target->hasRole(User::ROLE_AGENT_RDV));
    }

    public function test_admin_update_with_new_password_sets_must_change_password(): void
    {
        $admin = $this->makeAdmin();
        $target = User::factory()->create([
            'must_change_password' => false,
            'account_status' => AccountStatusEnum::ACTIF,
        ]);

        $this->actingAs($admin)->put(route('admin.users.update', $target), $this->identityFields([
            'name' => $target->name,
            'email' => $target->email,
            'phone' => self::VALID_PHONE,
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]))->assertRedirect(route('admin.users.edit', $target));

        $target->refresh();
        $this->assertTrue($target->must_change_password);
    }

    public function test_admin_can_reset_user_password_and_send_email(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $target = User::factory()->create([
            'email' => 'reset-target@example.com',
            'must_change_password' => false,
        ]);

        $oldHash = $target->password;

        $response = $this->actingAs($admin)->post(route('admin.users.reset-password', $target));

        $response->assertRedirect(route('admin.users.edit', $target));
        $response->assertSessionHas('success');

        $target->refresh();
        $this->assertTrue($target->must_change_password);
        $this->assertNotSame($oldHash, $target->password);

        Mail::assertSent(AdminUserTemporaryPasswordMail::class, function (AdminUserTemporaryPasswordMail $mail) use ($target) {
            return $mail->hasTo($target->email)
                && $mail->user->is($target->fresh())
                && strlen($mail->temporaryPassword) >= 12
                && Hash::check($mail->temporaryPassword, $target->fresh()->password);
        });
    }

    public function test_admin_cannot_reset_own_password_via_admin_action(): void
    {
        Mail::fake();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('admin.users.reset-password', $admin))
            ->assertRedirect(route('admin.users.edit', $admin))
            ->assertSessionHas('error');

        Mail::assertNothingSent();
    }

    public function test_non_admin_cannot_reset_user_password(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($user)->post(route('admin.users.reset-password', $target))
            ->assertForbidden();

        Mail::assertNothingSent();
    }

    public function test_guest_cannot_create_user(): void
    {
        $this->post(route('admin.users.store'), [
            'name' => 'Intrusion',
            'email' => 'intrusion@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.users.store'), [
            'name' => 'Interdit',
            'email' => 'interdit@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])->assertForbidden();
    }

    public function test_non_admin_cannot_update_user(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create(['name' => 'Cible']);

        $this->actingAs($user)->put(route('admin.users.update', $target), [
            'name' => 'Modifié',
            'email' => $target->email,
        ])->assertForbidden();

        $target->refresh();
        $this->assertSame('Cible', $target->name);
    }
}
