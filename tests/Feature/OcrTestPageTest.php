<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OCR\OcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class OcrTestPageTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    /** @test */
    public function admin_ocr_test_form_accepts_pdf(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.ocr.test'))
            ->assertOk()
            ->assertSee('application/pdf')
            ->assertSee('JPG, PNG, WEBP ou PDF');
    }

    /** @test */
    public function admin_can_submit_a_pdf_for_ocr_test(): void
    {
        $admin = $this->makeAdmin();

        $this->mock(OcrService::class, function ($mock) {
            $mock->shouldReceive('extract')->once()->andReturn("NOM : DUPONT\nNIF : 000-000-000-0");
        });

        $pdf = UploadedFile::fake()->create('cin.pdf', 120, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('admin.ocr.test.store'), ['document' => $pdf])
            ->assertOk()
            ->assertSee('JSON (clé / valeur)')
            ->assertSee('&quot;nom&quot;: &quot;DUPONT&quot;', false)
            ->assertSee('&quot;nif&quot;: &quot;0000000000&quot;', false);
    }
}
