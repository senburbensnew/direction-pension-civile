<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PensionSchemaDeprecationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_table_no_longer_has_pension_code_column(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'pension_code'));
    }

    /** @test */
    public function pension_code_is_resolved_from_affiliation_not_user_column(): void
    {
        $user = $this->createPensionneUser([], '8-77777');

        $this->assertSame('8-77777', $user->displayPensionCode());
        $this->assertSame($user->id, \App\Models\User::findByPensionCode('8-77777')?->id);
    }
}
